<?php

namespace Tests\Feature\Inventory;

use App\Enums\Role;
use App\Models\Product;
use App\Models\User;
use App\Policies\StockAdjustmentPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StockAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_warehouse_to_adjust_stock(): void
    {
        $policy = new StockAdjustmentPolicy;

        foreach ([Role::Admin, Role::Warehouse] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->assertTrue($policy->create($user));
        }
    }

    public function test_policy_denies_cashier_and_manager_to_adjust_stock(): void
    {
        $policy = new StockAdjustmentPolicy;

        foreach ([Role::Cashier, Role::Manager] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->assertFalse($policy->create($user));
        }
    }

    public function test_warehouse_can_access_stock_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($warehouse)->get(route('inventory.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Index'));
        $this->actingAs($warehouse)->get(route('inventory.movements'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Movements'));
        $this->actingAs($warehouse)->get(route('inventory.adjustments.create'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Adjustments/Create'));

        $this->actingAs($warehouse)->post(route('inventory.adjustments.store'), [
            'product_id' => $product->id,
            'new_stock' => 12,
            'reason' => 'Stok opname',
        ])->assertRedirect(route('inventory.index'));
    }

    public function test_admin_can_access_stock_pages(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();

        $this->actingAs($admin)->get(route('inventory.index'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Index'));
        $this->actingAs($admin)->get(route('inventory.movements'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Movements'));
        $this->actingAs($admin)->get(route('inventory.adjustments.create'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Inventory/Adjustments/Create'));
    }

    public function test_cashier_is_forbidden_on_stock_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $product = Product::factory()->create();

        $this->actingAs($cashier)->get(route('inventory.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('inventory.movements'))->assertForbidden();
        $this->actingAs($cashier)->get(route('inventory.adjustments.create'))->assertForbidden();
        $this->actingAs($cashier)->post(route('inventory.adjustments.store'), [
            'product_id' => $product->id,
            'new_stock' => 12,
            'reason' => 'Stok opname',
        ])->assertForbidden();
    }

    public function test_manager_is_forbidden_on_stock_pages(): void
    {
        $manager = User::factory()->withRole(Role::Manager)->create();

        $this->actingAs($manager)->get(route('inventory.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('inventory.movements'))->assertForbidden();
        $this->actingAs($manager)->get(route('inventory.adjustments.create'))->assertForbidden();
    }
}
