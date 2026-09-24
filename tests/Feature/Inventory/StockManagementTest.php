<?php

namespace Tests\Feature\Inventory;

use App\Enums\MovementType;
use App\Enums\Role;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Produk Menipis')
            ->assertSee('Produk Aman')
            ->assertSee('Stok menipis')
            ->assertSee('Aman');
    }

    public function test_low_stock_filter_only_returns_products_at_or_below_minimum(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        Product::factory()->create(['name' => 'Produk Menipis', 'stock' => 5, 'minimum_stock' => 10]);
        Product::factory()->create(['name' => 'Produk Aman', 'stock' => 50, 'minimum_stock' => 10]);

        $response = $this->actingAs($warehouse)->get(route('inventory.index', ['low_stock' => 1]));

        $response->assertOk()
            ->assertSee('Produk Menipis')
            ->assertDontSee('Produk Aman');
    }

    public function test_stock_page_filters_by_keyword(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        Product::factory()->create(['name' => 'Beras Premium', 'sku' => 'SKU-BERAS']);
        Product::factory()->create(['name' => 'Gula Pasir', 'sku' => 'SKU-GULA']);

        $response = $this->actingAs($warehouse)->get(route('inventory.index', ['keyword' => 'Beras']));

        $response->assertOk()
            ->assertSee('Beras Premium')
            ->assertDontSee('Gula Pasir');
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
            ->assertSee('Produk Riwayat')
            ->assertSee('Catatan penerimaan')
            ->assertSee('Catatan penyesuaian');

        $this->actingAs($warehouse)
            ->get(route('inventory.movements', ['movement_type' => MovementType::Adjustment->value]))
            ->assertOk()
            ->assertSee('Catatan penyesuaian')
            ->assertDontSee('Catatan penerimaan');
    }

    public function test_warehouse_can_open_adjustment_form_with_prefilled_product(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['name' => 'Produk Disiapkan']);

        $this->actingAs($warehouse)
            ->get(route('inventory.adjustments.create', ['product_id' => $product->id]))
            ->assertOk()
            ->assertSee('Penyesuaian Stok')
            ->assertSee('Produk Disiapkan');
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
