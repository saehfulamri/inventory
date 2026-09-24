<?php

namespace Tests\Feature\Services;

use App\Enums\MovementType;
use App\Enums\PurchaseStatus;
use App\Enums\Role;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\PurchaseService;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class PurchaseServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_makes_draft_purchase_with_items_and_total(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $supplier = Supplier::factory()->create();
        $productA = Product::factory()->create(['stock' => 10]);
        $productB = Product::factory()->create(['stock' => 20]);
        $service = app(PurchaseService::class);

        $purchase = $service->create([
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'notes' => 'Pesanan awal',
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 3, 'unit_price' => 10000],
                ['product_id' => $productB->id, 'quantity' => 2, 'unit_price' => 25000],
            ],
        ], $admin);

        $this->assertSame(PurchaseStatus::Draft, $purchase->status);
        $this->assertSame($supplier->id, $purchase->supplier_id);
        $this->assertSame($admin->id, $purchase->user_id);
        $this->assertStringStartsWith('PO-'.now()->format('Ymd').'-', $purchase->purchase_number);
        $this->assertSame('80000.00', $purchase->total_amount);
        $this->assertSame(2, $purchase->items()->count());
        $this->assertSame('30000.00', $purchase->items()->orderBy('id')->first()->subtotal);
        $this->assertSame(10.0, (float) $productA->fresh()->stock);
        $this->assertSame(20.0, (float) $productB->fresh()->stock);
    }

    public function test_create_generates_unique_purchase_numbers(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $service = app(PurchaseService::class);

        $first = $service->create([
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000]],
        ], $admin);

        $second = $service->create([
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000]],
        ], $admin);

        $this->assertNotSame($first->purchase_number, $second->purchase_number);
        $this->assertDatabaseCount('purchases', 2);
    }

    public function test_finalize_increases_stock_and_records_purchase_in_movement(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $purchase = $this->createPurchase($admin, $supplier, $product, 5, 2000);
        $service = app(PurchaseService::class);

        $completed = $service->finalize($purchase, $admin);

        $this->assertSame(PurchaseStatus::Completed, $completed->status);
        $this->assertSame(15.0, (float) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $admin->id,
            'movement_type' => MovementType::PurchaseIn->value,
            'quantity' => '5.000',
            'stock_before' => '10.000',
            'stock_after' => '15.000',
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
            'notes' => "Penerimaan {$purchase->purchase_number}",
        ]);
    }

    public function test_finalize_rejects_already_completed_purchase_without_double_stock(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $purchase = $this->createPurchase($admin, $supplier, $product, 5, 2000);
        $service = app(PurchaseService::class);

        $service->finalize($purchase, $admin);

        $this->expectException(ValidationException::class);
        try {
            $service->finalize($purchase, $admin);
        } finally {
            $this->assertSame(15.0, (float) $product->fresh()->stock);
            $this->assertDatabaseCount('stock_movements', 1);
        }
    }

    public function test_finalize_is_atomic_when_stock_update_fails(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $supplier = Supplier::factory()->create();
        $productA = Product::factory()->create(['stock' => 10]);
        $productB = Product::factory()->create(['stock' => 20]);
        $purchase = $this->createPurchaseWithItems($admin, $supplier, [
            ['product' => $productA, 'quantity' => 5, 'unit_price' => 1000],
            ['product' => $productB, 'quantity' => 5, 'unit_price' => 2000],
        ]);

        $failingStock = new class(app(ProductRepositoryInterface::class)) extends StockService
        {
            public int $calls = 0;

            public function increase(
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

                return parent::increase($product, $quantity, $type, $user, $reference, $notes);
            }
        };

        $this->app->instance(StockService::class, $failingStock);
        $service = app(PurchaseService::class);

        try {
            $service->finalize($purchase, $admin);
            $this->fail('finalize() seharusnya melempar exception.');
        } catch (RuntimeException) {
            // expected
        }

        $this->assertSame(10.0, (float) $productA->fresh()->stock);
        $this->assertSame(20.0, (float) $productB->fresh()->stock);
        $this->assertSame(PurchaseStatus::Draft, $purchase->fresh()->status);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_paginate_filters_by_supplier_and_status(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);
        $draft = $this->createPurchase($admin, $supplier, $product, 1, 1000);
        $completed = Purchase::factory()->for($supplier)->for($admin)->completed()->create(['purchase_number' => 'PO-20260921-9999']);

        $service = app(PurchaseService::class);

        $bySupplier = $service->paginate(['supplier_id' => $supplier->id]);
        $this->assertSame(2, $bySupplier->total());

        $byStatus = $service->paginate(['status' => PurchaseStatus::Completed->value]);
        $this->assertSame(1, $byStatus->total());
        $this->assertTrue($byStatus->first()->is($completed));

        $byKeyword = $service->paginate(['keyword' => '9999']);
        $this->assertSame(1, $byKeyword->total());

        $this->assertTrue($service->findById($draft->id)->is($draft));
        $this->assertDatabaseHas('purchases', ['id' => $completed->id, 'status' => PurchaseStatus::Completed->value]);
    }

    private function createPurchase(User $user, Supplier $supplier, Product $product, float $qty, float $unitPrice): Purchase
    {
        return $this->createPurchaseWithItems($user, $supplier, [
            ['product' => $product, 'quantity' => $qty, 'unit_price' => $unitPrice],
        ]);
    }

    private function createPurchaseWithItems(User $user, Supplier $supplier, array $items): Purchase
    {
        return app(PurchaseService::class)->create([
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => array_map(fn (array $item) => [
                'product_id' => $item['product']->id,
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ], $items),
        ], $user);
    }
}
