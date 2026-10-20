<?php

namespace Tests\Feature\Units;

use App\Enums\Role;
use App\Models\Unit;
use App\Models\User;
use App\Policies\UnitPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class UnitAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_warehouse(): void
    {
        $policy = new UnitPolicy;
        $unit = Unit::factory()->create();

        $admin = User::factory()->withRole(Role::Admin)->create();
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        foreach ([$admin, $warehouse] as $user) {
            $this->assertTrue($policy->viewAny($user));
            $this->assertTrue($policy->create($user));
            $this->assertTrue($policy->update($user, $unit));
            $this->assertTrue($policy->delete($user, $unit));
        }
    }

    public function test_policy_denies_cashier_and_manager(): void
    {
        $policy = new UnitPolicy;
        $unit = Unit::factory()->create();

        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $manager = User::factory()->withRole(Role::Manager)->create();

        foreach ([$cashier, $manager] as $user) {
            $this->assertFalse($policy->viewAny($user));
            $this->assertFalse($policy->create($user));
            $this->assertFalse($policy->update($user, $unit));
            $this->assertFalse($policy->delete($user, $unit));
        }
    }

    public function test_warehouse_can_access_unit_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('units.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Index')
                ->where('can.viewAnyUnits', true)
            );

        $this->actingAs($warehouse)
            ->get(route('units.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Units/Create')
                ->where('can.viewAnyUnits', true)
            );
    }

    public function test_cashier_is_forbidden_on_unit_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $unit = Unit::factory()->create();

        $this->actingAs($cashier)->get(route('units.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('units.create'))->assertForbidden();
        $this->actingAs($cashier)->get(route('units.edit', $unit))->assertForbidden();
        $this->actingAs($cashier)->post(route('units.store'), ['name' => 'X', 'symbol' => 'x'])->assertForbidden();
        $this->actingAs($cashier)->put(route('units.update', $unit), ['name' => 'Y', 'symbol' => 'y'])->assertForbidden();
        $this->actingAs($cashier)->post(route('units.deactivate', $unit))->assertForbidden();
    }
}
