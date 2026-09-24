<?php

namespace Tests\Feature\Database;

use App\Enums\MovementType;
use App\Enums\PurchaseStatus;
use App\Enums\Role;
use App\Enums\SaleStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category_and_unit(): void
    {
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);

        $this->assertTrue($product->category->is($category));
        $this->assertTrue($product->unit->is($unit));
        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($unit->products->contains($product));
    }

    public function test_supplier_has_purchases(): void
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->withRole(Role::Warehouse)->create();

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'purchase_number' => 'PO-2026-0001',
            'purchase_date' => now(),
            'status' => PurchaseStatus::Completed,
            'total_amount' => 10000,
            'notes' => null,
        ]);

        $this->assertTrue($purchase->supplier->is($supplier));
        $this->assertTrue($purchase->user->is($user));
        $this->assertTrue($supplier->purchases->contains($purchase));
        $this->assertSame(PurchaseStatus::Completed, $purchase->status);
    }

    public function test_purchase_item_belongs_to_purchase_and_product(): void
    {
        $supplier = Supplier::factory()->create();
        $user = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create();

        $purchase = Purchase::create([
            'supplier_id' => $supplier->id,
            'user_id' => $user->id,
            'purchase_number' => 'PO-2026-0002',
            'purchase_date' => now(),
            'status' => PurchaseStatus::Draft,
            'total_amount' => 5000,
        ]);

        $item = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 1000,
            'subtotal' => 5000,
        ]);

        $this->assertTrue($item->purchase->is($purchase));
        $this->assertTrue($item->product->is($product));
        $this->assertTrue($product->purchaseItems->contains($item));
        $this->assertTrue($purchase->items->contains($item));
    }

    public function test_sale_and_sale_items_relationships(): void
    {
        $user = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create();

        $sale = Sale::create([
            'user_id' => $user->id,
            'sale_number' => 'TRX-2026-0001',
            'sale_date' => now(),
            'subtotal' => 7000,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 7000,
            'paid_amount' => 10000,
            'change_amount' => 3000,
            'payment_method' => 'cash',
            'status' => SaleStatus::Completed,
        ]);

        $item = SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 3500,
            'discount_amount' => 0,
            'subtotal' => 7000,
        ]);

        $this->assertTrue($sale->user->is($user));
        $this->assertTrue($sale->items->contains($item));
        $this->assertTrue($item->sale->is($sale));
        $this->assertTrue($item->product->is($product));
        $this->assertTrue($product->saleItems->contains($item));
        $this->assertSame(SaleStatus::Completed, $sale->status);
    }

    public function test_stock_movement_reference_morphs_to_sale(): void
    {
        $user = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create();

        $sale = Sale::create([
            'user_id' => $user->id,
            'sale_number' => 'TRX-2026-0002',
            'sale_date' => now(),
            'subtotal' => 3500,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'grand_total' => 3500,
            'paid_amount' => 3500,
            'change_amount' => 0,
            'payment_method' => 'cash',
            'status' => SaleStatus::Completed,
        ]);

        $movement = StockMovement::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'movement_type' => MovementType::SaleOut,
            'quantity' => 1,
            'stock_before' => 10,
            'stock_after' => 9,
            'reference_type' => Sale::class,
            'reference_id' => $sale->id,
            'notes' => null,
        ]);

        $this->assertTrue($movement->product->is($product));
        $this->assertTrue($movement->user->is($user));
        $this->assertInstanceOf(Sale::class, $movement->reference);
        $this->assertTrue($movement->reference->is($sale));
        $this->assertTrue($product->stockMovements->contains($movement));
        $this->assertSame(MovementType::SaleOut, $movement->movement_type);
    }

    public function test_product_sku_must_be_unique(): void
    {
        $product = Product::factory()->create();

        $this->expectException(QueryException::class);

        Product::factory()->create(['sku' => $product->sku]);
    }

    public function test_user_role_is_cast_to_enum(): void
    {
        $user = User::factory()->withRole(Role::Admin)->create();

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertSame(Role::Admin, $user->role);
        $this->assertSame('Admin', $user->role->label());
    }

    public function test_user_role_defaults_to_cashier(): void
    {
        $user = User::factory()->create();

        $this->assertSame(Role::Cashier, $user->role);
    }
}
