<?php

namespace Tests\Feature\Services;

use App\Enums\MovementType;
use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\SaleStatus;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\SaleService;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_creates_completed_sale_with_items_and_movements(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $productA = Product::factory()->create(['stock' => 10, 'selling_price' => 15000]);
        $productB = Product::factory()->create(['stock' => 20, 'selling_price' => 5000]);
        $service = app(SaleService::class);

        $sale = $service->complete([
            'sale_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::Cash->value,
            'paid_amount' => 50000,
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 2],
                ['product_id' => $productB->id, 'quantity' => 3],
            ],
        ], $cashier);

        $this->assertSame(SaleStatus::Completed, $sale->status);
        $this->assertSame($cashier->id, $sale->user_id);
        $this->assertStringStartsWith('SO-'.now()->format('Ymd').'-', $sale->sale_number);
        $this->assertSame('45000.00', $sale->subtotal);
        $this->assertSame('45000.00', $sale->grand_total);
        $this->assertSame('50000.00', $sale->paid_amount);
        $this->assertSame('5000.00', $sale->change_amount);
        $this->assertSame(PaymentMethod::Cash, $sale->payment_method);
        $this->assertSame(2, $sale->items()->count());

        $this->assertSame(8.0, (float) $productA->fresh()->stock);
        $this->assertSame(17.0, (float) $productB->fresh()->stock);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $productA->id,
            'quantity' => '2.000',
            'unit_price' => '15000.00',
            'subtotal' => '30000.00',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $productA->id,
            'user_id' => $cashier->id,
            'movement_type' => MovementType::SaleOut->value,
            'quantity' => '2.000',
            'stock_before' => '10.000',
            'stock_after' => '8.000',
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'notes' => "Penjualan {$sale->sale_number}",
        ]);
    }

    public function test_complete_non_cash_sets_paid_equal_to_total(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 10000]);
        $service = app(SaleService::class);

        $sale = $service->complete([
            'sale_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::Qris->value,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ], $cashier);

        $this->assertSame('20000.00', $sale->paid_amount);
        $this->assertSame('0.00', $sale->change_amount);
        $this->assertSame(PaymentMethod::Qris, $sale->payment_method);
    }

    public function test_complete_rejects_paid_less_than_total(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 10000]);
        $service = app(SaleService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->complete([
                'sale_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::Cash->value,
                'paid_amount' => 1000,
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ], $cashier);
        } finally {
            $this->assertSame(10.0, (float) $product->fresh()->stock);
            $this->assertDatabaseCount('sales', 0);
            $this->assertDatabaseCount('sale_items', 0);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_complete_rejects_insufficient_stock(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 3, 'selling_price' => 10000]);
        $service = app(SaleService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->complete([
                'sale_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::Cash->value,
                'paid_amount' => 100000,
                'items' => [['product_id' => $product->id, 'quantity' => 4]],
            ], $cashier);
        } finally {
            $this->assertSame(3.0, (float) $product->fresh()->stock);
            $this->assertDatabaseCount('sales', 0);
            $this->assertDatabaseCount('sale_items', 0);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_complete_rejects_inactive_product(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'is_active' => false]);
        $service = app(SaleService::class);

        $this->expectException(ValidationException::class);

        try {
            $service->complete([
                'sale_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::Cash->value,
                'paid_amount' => 100000,
                'items' => [['product_id' => $product->id, 'quantity' => 1]],
            ], $cashier);
        } finally {
            $this->assertDatabaseCount('sales', 0);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }

    public function test_complete_uses_server_side_selling_price(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 20000]);
        $service = app(SaleService::class);

        $sale = $service->complete([
            'sale_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::Cash->value,
            'paid_amount' => 100000,
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ], $cashier);

        $this->assertSame('60000.00', $sale->grand_total);
        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'unit_price' => '20000.00',
            'subtotal' => '60000.00',
        ]);
    }

    public function test_complete_generates_unique_sale_numbers(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);
        $service = app(SaleService::class);

        $first = $service->complete([
            'sale_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::Cash->value,
            'paid_amount' => 1000,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ], $cashier);

        $second = $service->complete([
            'sale_date' => now()->toDateString(),
            'payment_method' => PaymentMethod::Cash->value,
            'paid_amount' => 1000,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ], $cashier);

        $this->assertNotSame($first->sale_number, $second->sale_number);
        $this->assertDatabaseCount('sales', 2);
    }

    public function test_complete_is_atomic_when_stock_decrease_fails(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $productA = Product::factory()->create(['stock' => 10, 'selling_price' => 1000]);
        $productB = Product::factory()->create(['stock' => 20, 'selling_price' => 1000]);

        $failingStock = new class(app(ProductRepositoryInterface::class)) extends StockService
        {
            public int $calls = 0;

            public function decrease(
                Product $product,
                float $quantity,
                MovementType $type,
                ?User $user = null,
                ?Model $reference = null,
                ?string $notes = null,
            ): Product {
                $this->calls++;

                if ($this->calls === 2) {
                    throw new RuntimeException('simulated failure');
                }

                return parent::decrease($product, $quantity, $type, $user, $reference, $notes);
            }
        };

        $this->app->instance(StockService::class, $failingStock);
        $service = app(SaleService::class);

        try {
            $service->complete([
                'sale_date' => now()->toDateString(),
                'payment_method' => PaymentMethod::Cash->value,
                'paid_amount' => 100000,
                'items' => [
                    ['product_id' => $productA->id, 'quantity' => 2],
                    ['product_id' => $productB->id, 'quantity' => 3],
                ],
            ], $cashier);
            $this->fail('complete() seharusnya melempar exception.');
        } catch (RuntimeException) {
            // expected
        }

        $this->assertSame(10.0, (float) $productA->fresh()->stock);
        $this->assertSame(20.0, (float) $productB->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('sale_items', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_paginate_filters_by_status_and_sale_number(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $sale = Sale::factory()->for($cashier)->completed()->create(['sale_number' => 'SO-20260921-0000001']);
        Sale::factory()->for($cashier)->create(['sale_number' => 'SO-20260921-0000002']);

        $service = app(SaleService::class);

        $this->assertSame(2, $service->paginate()->total());
        $this->assertSame(2, $service->paginate(['status' => SaleStatus::Completed->value])->total());

        $byKeyword = $service->paginate(['keyword' => '0000001']);
        $this->assertSame(1, $byKeyword->total());
        $this->assertTrue($byKeyword->first()->is($sale));

        $this->assertTrue($service->findById($sale->id)->is($sale));
    }
}
