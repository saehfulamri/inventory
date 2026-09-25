<?php

namespace Tests\Feature\Sales;

use App\Enums\MovementType;
use App\Enums\Role;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SaleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_can_open_pos_page(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        $this->actingAs($cashier)
            ->get(route('sales.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Pos')
                ->where('paymentMethods.0.value', 'cash')
                ->where('paymentMethods.0.label', 'Tunai')
                ->where('paymentMethods.3.value', 'card')
            );
    }

    public function test_products_endpoint_searches_active_products_with_stock(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $inStock = Product::factory()->create(['name' => 'Beras Premium', 'stock' => 10, 'selling_price' => 15000]);
        Product::factory()->create(['name' => 'Gula Pasir', 'stock' => 10]);
        Product::factory()->create(['name' => 'Terigu', 'stock' => 0]);
        Product::factory()->create(['name' => 'Beras Nonaktif', 'stock' => 10, 'is_active' => false]);

        $response = $this->actingAs($cashier)
            ->get(route('sales.products', ['q' => 'Beras']))
            ->assertOk();

        $products = $response->json();

        $this->assertCount(1, $products);
        $this->assertSame($inStock->id, $products[0]['id']);
        $this->assertSame('Beras Premium', $products[0]['name']);
        $this->assertSame(15000, (int) $products[0]['price']);
        $this->assertSame(10, (int) $products[0]['stock']);
    }

    public function test_products_endpoint_returns_products_when_no_keyword(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        Product::factory()->create(['name' => 'Beras Premium', 'stock' => 10]);
        Product::factory()->create(['name' => 'Gula Pasir', 'stock' => 10]);

        $response = $this->actingAs($cashier)->get(route('sales.products'))->assertOk();

        $this->assertCount(2, $response->json());
    }

    public function test_cashier_can_complete_a_sale_via_http(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 15000]);

        $response = $this->actingAs($cashier)->post(route('sales.store'), [
            'sale_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);

        $response->assertRedirect();
        $this->assertSame(8.0, (float) $product->fresh()->stock);
        $this->assertDatabaseHas('sales', ['status' => 'completed', 'grand_total' => '30000.00', 'change_amount' => '20000.00']);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => MovementType::SaleOut->value,
            'quantity' => '2.000',
        ]);
        $this->assertDatabaseHas('sale_items', [
            'unit_price' => '15000.00',
            'quantity' => '2.000',
            'subtotal' => '30000.00',
        ]);
    }

    public function test_sale_requires_items_and_valid_payment(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        $this->actingAs($cashier)
            ->post(route('sales.store'), [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
            ])
            ->assertSessionHasErrors(['items', 'paid_amount']);
    }

    public function test_sale_rejects_paid_less_than_total(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 10, 'selling_price' => 15000]);

        $this->actingAs($cashier)
            ->post(route('sales.store'), [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'paid_amount' => 1000,
                'items' => [['product_id' => $product->id, 'quantity' => 2]],
            ])
            ->assertSessionHasErrors('paid_amount');

        $this->assertSame(10.0, (float) $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_sale_rejects_insufficient_stock(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['stock' => 2, 'selling_price' => 15000]);

        $this->actingAs($cashier)
            ->post(route('sales.store'), [
                'sale_date' => now()->toDateString(),
                'payment_method' => 'cash',
                'paid_amount' => 100000,
                'items' => [['product_id' => $product->id, 'quantity' => 5]],
            ])
            ->assertSessionHasErrors('stock');

        $this->assertSame(2.0, (float) $product->fresh()->stock);
        $this->assertDatabaseCount('sales', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_history_page_lists_sales(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $sale = Sale::factory()->for($cashier)->completed()->create(['sale_number' => 'SO-20260921-0001234']);

        $this->actingAs($cashier)
            ->get(route('sales.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Index')
                ->where('sales.total', 1)
                ->where('sales.data.0.sale_number', 'SO-20260921-0001234')
            );
    }

    public function test_receipt_page_renders_sale_details(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create(['name' => 'Produk Struk']);
        $sale = Sale::factory()->for($cashier)->completed()->create();
        $sale->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 15000,
            'discount_amount' => 0,
            'subtotal' => 30000,
        ]);
        $sale->update([
            'subtotal' => 30000,
            'grand_total' => 30000,
            'paid_amount' => 50000,
            'change_amount' => 20000,
        ]);

        $this->actingAs($cashier)
            ->get(route('sales.show', $sale))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Sales/Show')
                ->where('sale.sale_number', $sale->sale_number)
                ->where('sale.grand_total', '30000.00')
                ->where('sale.paid_amount', '50000.00')
                ->where('sale.items.0.product.name', 'Produk Struk')
            );
    }
}
