<?php

namespace Tests\Feature\Inventory;

use App\Enums\MovementType;
use App\Enums\Role;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_page_lists_products_with_stock_status(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        Product::factory()->create(['name' => 'Produk Menipis', 'stock' => 5, 'minimum_stock' => 10]);
        Product::factory()->create(['name' => 'Produk Aman', 'stock' => 50, 'minimum_stock' => 10]);

        $response = $this->actingAs($warehouse)->get(route('inventory.index'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Index')
                ->where('products.total', 2)
                ->where('products.data.0.name', 'Produk Aman')
                ->where('products.data.1.name', 'Produk Menipis')
            );
    }

    public function test_low_stock_filter_only_returns_products_at_or_below_minimum(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        Product::factory()->create(['name' => 'Produk Menipis', 'stock' => 5, 'minimum_stock' => 10]);
        Product::factory()->create(['name' => 'Produk Aman', 'stock' => 50, 'minimum_stock' => 10]);

        $response = $this->actingAs($warehouse)->get(route('inventory.index', ['low_stock' => 1]));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Index')
                ->where('filters.low_stock', true)
                ->where('products.total', 1)
                ->where('products.data.0.name', 'Produk Menipis')
            );
    }

    public function test_stock_page_filters_by_keyword(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        Product::factory()->create(['name' => 'Beras Premium', 'sku' => 'SKU-BERAS']);
        Product::factory()->create(['name' => 'Gula Pasir', 'sku' => 'SKU-GULA']);

        $response = $this->actingAs($warehouse)->get(route('inventory.index', ['keyword' => 'Beras']));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Index')
                ->where('filters.keyword', 'Beras')
                ->where('products.total', 1)
                ->where('products.data.0.name', 'Beras Premium')
            );
    }

    public function test_movement_history_lists_and_filters_movements(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['name' => 'Produk Riwayat']);

        StockMovement::factory()->for($product)->create([
            'movement_type' => MovementType::PurchaseIn,
            'notes' => 'Catatan penerimaan',
        ]);
        StockMovement::factory()->for($product)->create([
            'movement_type' => MovementType::Adjustment,
            'notes' => 'Catatan penyesuaian',
        ]);

        $this->actingAs($warehouse)
            ->get(route('inventory.movements'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Movements')
                ->where('movements.total', 2)
                ->where('movements.data.0.product.name', 'Produk Riwayat')
                ->where('movements.data.1.product.name', 'Produk Riwayat')
            );

        $this->actingAs($warehouse)
            ->get(route('inventory.movements', ['movement_type' => MovementType::Adjustment->value]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Movements')
                ->where('filters.movement_type', 'adjustment')
                ->where('movements.total', 1)
                ->where('movements.data.0.movement_type', 'adjustment')
                ->where('movements.data.0.notes', 'Catatan penyesuaian')
            );
    }

    public function test_warehouse_can_open_adjustment_form_with_prefilled_product(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['name' => 'Produk Disiapkan']);

        $this->actingAs($warehouse)
            ->get(route('inventory.adjustments.create', ['product_id' => $product->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Inventory/Adjustments/Create')
                ->where('selectedProductId', $product->id)
                ->where('products.0.id', $product->id)
                ->where('products.0.name', 'Produk Disiapkan')
            );
    }

    public function test_warehouse_can_submit_adjustment_and_stock_is_updated(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->actingAs($warehouse)->post(route('inventory.adjustments.store'), [
            'product_id' => $product->id,
            'new_stock' => 25,
            'reason' => 'Hasil stok opname',
        ]);

        $response->assertRedirect(route('inventory.index'))
            ->assertSessionHas('success');

        $this->assertSame(25.0, (float) $product->fresh()->stock);
        $this->assertDatabaseHas('stock_adjustments', [
            'product_id' => $product->id,
            'user_id' => $warehouse->id,
            'difference' => '15.000',
            'reason' => 'Hasil stok opname',
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'movement_type' => MovementType::Adjustment->value,
            'quantity' => '15.000',
            'stock_before' => '10.000',
            'stock_after' => '25.000',
        ]);
    }

    public function test_adjustment_requires_product_new_stock_and_reason(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->post(route('inventory.adjustments.store'), [])
            ->assertSessionHasErrors(['product_id', 'new_stock', 'reason']);
    }

    public function test_adjustment_rejects_negative_new_stock(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($warehouse)
            ->post(route('inventory.adjustments.store'), [
                'product_id' => $product->id,
                'new_stock' => -1,
                'reason' => 'Salah input',
            ])
            ->assertSessionHasErrors('new_stock');

        $this->assertSame(10.0, (float) $product->fresh()->stock);
    }

    public function test_adjustment_rejects_unchanged_stock(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($warehouse)
            ->post(route('inventory.adjustments.store'), [
                'product_id' => $product->id,
                'new_stock' => 10,
                'reason' => 'Tidak ada perubahan',
            ])
            ->assertSessionHasErrors('new_stock');

        $this->assertDatabaseCount('stock_adjustments', 0);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
