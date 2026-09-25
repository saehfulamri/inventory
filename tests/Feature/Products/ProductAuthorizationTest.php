<?php

namespace Tests\Feature\Products;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_warehouse(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $admin = User::factory()->withRole(Role::Admin)->create();
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $product));
        $this->assertTrue($policy->delete($admin, $product));

        $this->assertTrue($policy->viewAny($warehouse));
        $this->assertTrue($policy->create($warehouse));
        $this->assertTrue($policy->update($warehouse, $product));
        $this->assertTrue($policy->delete($warehouse, $product));
    }

    public function test_policy_denies_cashier_and_manager(): void
    {
        $policy = new ProductPolicy;
        $product = Product::factory()->create();

        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $manager = User::factory()->withRole(Role::Manager)->create();

        $this->assertFalse($policy->viewAny($cashier));
        $this->assertFalse($policy->create($cashier));
        $this->assertFalse($policy->update($cashier, $product));
        $this->assertFalse($policy->delete($cashier, $product));

        $this->assertFalse($policy->viewAny($manager));
        $this->assertFalse($policy->create($manager));
        $this->assertFalse($policy->update($manager, $product));
        $this->assertFalse($policy->delete($manager, $product));
    }

    public function test_warehouse_can_access_product_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->where('can.viewAnyProducts', true)
            );

        $this->actingAs($warehouse)
            ->get(route('products.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Create')
                ->where('can.viewAnyProducts', true)
            );
    }

    public function test_cashier_is_forbidden_on_product_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create();
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($cashier)->get(route('products.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('products.create'))->assertForbidden();
        $this->actingAs($cashier)->get(route('products.edit', $product))->assertForbidden();
        $this->actingAs($cashier)->post(route('products.store'), [
            'sku' => 'SKU-CASH-1',
            'name' => 'Test',
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'purchase_price' => 10000,
            'selling_price' => 13000,
            'minimum_stock' => 5,
        ])->assertForbidden();
        $this->actingAs($cashier)->put(route('products.update', $product), [
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'sku' => $product->sku,
            'name' => 'Ubah',
            'purchase_price' => 10000,
            'selling_price' => 13000,
            'minimum_stock' => 5,
        ])->assertForbidden();
        $this->actingAs($cashier)->post(route('products.deactivate', $product))->assertForbidden();
    }

    public function test_manager_is_forbidden_on_product_pages(): void
    {
        $manager = User::factory()->withRole(Role::Manager)->create();
        $product = Product::factory()->create();

        $this->actingAs($manager)->get(route('products.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('products.create'))->assertForbidden();
        $this->actingAs($manager)->post(route('products.deactivate', $product))->assertForbidden();
    }
}
