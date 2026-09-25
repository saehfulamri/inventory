<?php

namespace Tests\Feature\Purchases;

use App\Enums\Role;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PurchaseReceivingTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_list_shows_empty_state(): void
    {
        $this->actingAs($this->warehouse())
            ->get(route('purchases.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Purchases/Index')
                ->where('purchases.total', 0)
                ->where('purchases.data', [])
            );
    }

    public function test_warehouse_can_open_create_form(): void
    {
        $this->actingAs($this->warehouse())
            ->get(route('purchases.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Purchases/Create'));
    }

    public function test_store_creates_draft_without_changing_stock(): void
    {
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($this->warehouse())
            ->post(route('purchases.store'), $this->payload($supplier, [$this->item($product, 5, 2000)]))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchases', ['status' => 'draft', 'total_amount' => '10000.00']);
        $this->assertDatabaseHas('purchase_items', ['quantity' => '5.000', 'unit_price' => '2000.00', 'subtotal' => '10000.00']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => '10.000']);
        $this->assertDatabaseCount('stock_movements', 0);
    }

    public function test_store_requires_at_least_one_item(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin())
            ->from(route('purchases.create'))
            ->post(route('purchases.store'), $this->payload($supplier, []))
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('items');
    }

    public function test_store_rejects_unknown_product(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin())
            ->from(route('purchases.create'))
            ->post(route('purchases.store'), $this->payload($supplier, [
                ['product_id' => 99999, 'quantity' => 5, 'unit_price' => 1000],
            ]))
            ->assertRedirect(route('purchases.create'))
            ->assertSessionHasErrors('items.0.product_id');
    }

    public function test_finalize_increases_stock_and_records_movement(): void
    {
        $warehouse = $this->warehouse();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($warehouse)
            ->post(route('purchases.store'), $this->payload($supplier, [$this->item($product, 5, 2000)]));

        $purchase = Purchase::first();

        $this->actingAs($warehouse)
            ->post(route('purchases.finalize', $purchase))
            ->assertRedirect(route('purchases.show', $purchase))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('purchases', ['id' => $purchase->id, 'status' => 'completed']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => '15.000']);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'user_id' => $warehouse->id,
            'movement_type' => 'purchase_in',
            'quantity' => '5.000',
            'stock_before' => '10.000',
            'stock_after' => '15.000',
            'reference_type' => Purchase::class,
            'reference_id' => $purchase->id,
        ]);
    }

    public function test_finalized_purchase_cannot_be_finalized_twice(): void
    {
        $warehouse = $this->warehouse();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($warehouse)
            ->post(route('purchases.store'), $this->payload($supplier, [$this->item($product, 5, 2000)]));

        $purchase = Purchase::first();

        $this->actingAs($warehouse)
            ->from(route('purchases.show', $purchase))
            ->post(route('purchases.finalize', $purchase));

        $this->actingAs($warehouse)
            ->from(route('purchases.show', $purchase))
            ->post(route('purchases.finalize', $purchase))
            ->assertRedirect(route('purchases.show', $purchase))
            ->assertSessionHasErrors('purchase');

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => '15.000']);
        $this->assertDatabaseCount('stock_movements', 1);
    }

    public function test_warehouse_can_view_purchase_detail(): void
    {
        $warehouse = $this->warehouse();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create(['name' => 'Air Mineral 600ml', 'sku' => 'SKU-AIR']);

        $purchase = Purchase::factory()->for($supplier)->for($warehouse)->create();
        PurchaseItem::factory()->for($purchase)->for($product)->create([
            'quantity' => 5,
            'unit_price' => 2000,
            'subtotal' => 10000,
        ]);
        $purchase->update(['total_amount' => 10000]);

        $this->actingAs($warehouse)
            ->get(route('purchases.show', $purchase))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Purchases/Show')
                ->where('purchase.purchase_number', $purchase->purchase_number)
                ->where('purchase.status', 'draft')
                ->where('purchase.supplier.name', $supplier->name)
                ->where('purchase.items.0.product.name', 'Air Mineral 600ml')
            );
    }

    private function admin(): User
    {
        return User::factory()->withRole(Role::Admin)->create();
    }

    private function warehouse(): User
    {
        return User::factory()->withRole(Role::Warehouse)->create();
    }

    private function payload(Supplier $supplier, array $items, array $overrides = []): array
    {
        return array_merge([
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'notes' => null,
            'items' => $items,
        ], $overrides);
    }

    private function item(Product $product, float $qty = 5, float $unitPrice = 1000): array
    {
        return [
            'product_id' => $product->id,
            'quantity' => $qty,
            'unit_price' => $unitPrice,
        ];
    }
}
