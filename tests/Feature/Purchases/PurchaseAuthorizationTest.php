<?php

namespace Tests\Feature\Purchases;

use App\Enums\Role;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\PurchasePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_warehouse(): void
    {
        $policy = new PurchasePolicy;
        $purchase = Purchase::factory()->create();

        foreach ([Role::Admin, Role::Warehouse] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->assertTrue($policy->viewAny($user));
            $this->assertTrue($policy->view($user, $purchase));
            $this->assertTrue($policy->create($user));
            $this->assertTrue($policy->finalize($user, $purchase));
        }
    }

    public function test_policy_denies_cashier_and_manager(): void
    {
        $policy = new PurchasePolicy;
        $purchase = Purchase::factory()->create();

        foreach ([Role::Cashier, Role::Manager] as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->assertFalse($policy->viewAny($user));
            $this->assertFalse($policy->view($user, $purchase));
            $this->assertFalse($policy->create($user));
            $this->assertFalse($policy->finalize($user, $purchase));
        }
    }

    public function test_warehouse_can_access_receiving_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();
        $purchase = Purchase::factory()->for($warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('purchases.index'))
            ->assertOk()
            ->assertSee('Penerimaan');

        $this->actingAs($warehouse)
            ->get(route('purchases.create'))
            ->assertOk()
            ->assertSee('Tambah Penerimaan');

        $this->actingAs($warehouse)
            ->get(route('purchases.show', $purchase))
            ->assertOk();
    }

    public function test_cashier_is_forbidden_on_receiving_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $supplier = Supplier::factory()->create();
        $product = Product::factory()->create();
        $purchase = Purchase::factory()->create();

        $this->actingAs($cashier)->get(route('purchases.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('purchases.create'))->assertForbidden();
        $this->actingAs($cashier)->get(route('purchases.show', $purchase))->assertForbidden();
        $this->actingAs($cashier)->post(route('purchases.store'), [
            'supplier_id' => $supplier->id,
            'purchase_date' => now()->toDateString(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5, 'unit_price' => 1000],
            ],
        ])->assertForbidden();
        $this->actingAs($cashier)->post(route('purchases.finalize', $purchase))->assertForbidden();
    }

    public function test_manager_is_forbidden_on_receiving_pages(): void
    {
        $manager = User::factory()->withRole(Role::Manager)->create();
        $purchase = Purchase::factory()->create();

        $this->actingAs($manager)->get(route('purchases.index'))->assertForbidden();
        $this->actingAs($manager)->get(route('purchases.create'))->assertForbidden();
        $this->actingAs($manager)->post(route('purchases.finalize', $purchase))->assertForbidden();
    }
}
