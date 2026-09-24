<?php

namespace Tests\Feature\Sales;

use App\Enums\Role;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Policies\SalePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_cashier(): void
    {
        $sale = Sale::factory()->create();

        foreach ([Role::Admin, Role::Cashier] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->assertTrue(app(SalePolicy::class)->viewAny($user));
            $this->assertTrue(app(SalePolicy::class)->view($user, $sale));
            $this->assertTrue(app(SalePolicy::class)->create($user));
        }
    }

    public function test_policy_denies_warehouse_and_manager(): void
    {
        $sale = Sale::factory()->create();

        foreach ([Role::Warehouse, Role::Manager] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->assertFalse(app(SalePolicy::class)->viewAny($user));
            $this->assertFalse(app(SalePolicy::class)->view($user, $sale));
            $this->assertFalse(app(SalePolicy::class)->create($user));
        }
    }

    public function test_cashier_can_access_sale_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $sale = Sale::factory()->for($cashier)->create();

        $this->actingAs($cashier)->get(route('sales.index'))->assertOk();
        $this->actingAs($cashier)->get(route('sales.create'))->assertOk();
        $this->actingAs($cashier)->get(route('sales.products'))->assertOk();
        $this->actingAs($cashier)->get(route('sales.show', $sale))->assertOk();
    }

    public function test_admin_can_access_sale_pages(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        $sale = Sale::factory()->for($admin)->create();

        $this->actingAs($admin)->get(route('sales.index'))->assertOk();
        $this->actingAs($admin)->get(route('sales.create'))->assertOk();
        $this->actingAs($admin)->get(route('sales.products'))->assertOk();
        $this->actingAs($admin)->get(route('sales.show', $sale))->assertOk();
    }

    public function test_warehouse_is_forbidden_on_sale_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $product = Product::factory()->create();
        $sale = Sale::factory()->create();

        $this->actingAs($warehouse)->get(route('sales.index'))->assertForbidden();
        $this->actingAs($warehouse)->get(route('sales.create'))->assertForbidden();
        $this->actingAs($warehouse)->get(route('sales.products'))->assertForbidden();
        $this->actingAs($warehouse)->get(route('sales.show', $sale))->assertForbidden();
        $this->actingAs($warehouse)->post(route('sales.store'), [
            'sale_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'paid_amount' => 1000,
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ])->assertForbidden();
    }

    public function test_manager_is_forbidden_on_sale_pages(): void
    {
        $manager = User::factory()->withRole(Role::Manager)->create();
        $sale = Sale::factory()->create();

        $this->actingAs($manager)->get(route('sales.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('sales.create'))->assertForbidden();
        $this->actingAs($manager)->get(route('sales.show', $sale))->assertForbidden();
    }
}
