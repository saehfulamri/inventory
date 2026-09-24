<?php

namespace Tests\Feature\Suppliers;

use App\Enums\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Policies\SupplierPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_warehouse(): void
    {
        $policy = new SupplierPolicy;
        $supplier = Supplier::factory()->create();

        $admin = User::factory()->withRole(Role::Admin)->create();
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        foreach ([$admin, $warehouse] as $user) {
            $this->assertTrue($policy->viewAny($user));
            $this->assertTrue($policy->create($user));
            $this->assertTrue($policy->update($user, $supplier));
            $this->assertTrue($policy->delete($user, $supplier));
        }
    }

    public function test_policy_denies_cashier_and_manager(): void
    {
        $policy = new SupplierPolicy;
        $supplier = Supplier::factory()->create();

        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $manager = User::factory()->withRole(Role::Manager)->create();

        foreach ([$cashier, $manager] as $user) {
            $this->assertFalse($policy->viewAny($user));
            $this->assertFalse($policy->create($user));
            $this->assertFalse($policy->update($user, $supplier));
            $this->assertFalse($policy->delete($user, $supplier));
        }
    }

    public function test_warehouse_can_access_supplier_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Supplier');

        $this->actingAs($warehouse)
            ->get(route('suppliers.create'))
            ->assertOk()
            ->assertSee('Tambah Supplier');
    }

    public function test_cashier_is_forbidden_on_supplier_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $supplier = Supplier::factory()->create();

        $this->actingAs($cashier)->get(route('suppliers.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('suppliers.create'))->assertForbidden();
        $this->actingAs($cashier)->get(route('suppliers.edit', $supplier))->assertForbidden();
        $this->actingAs($cashier)->post(route('suppliers.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($cashier)->put(route('suppliers.update', $supplier), ['name' => 'Y'])->assertForbidden();
        $this->actingAs($cashier)->post(route('suppliers.deactivate', $supplier))->assertForbidden();
    }
}
