<?php

namespace Tests\Feature\Categories;

use App\Enums\Role;
use App\Models\Category;
use App\Models\User;
use App\Policies\CategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoryAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_allows_admin_and_warehouse(): void
    {
        $policy = new CategoryPolicy;
        $category = Category::factory()->create();

        $admin = User::factory()->withRole(Role::Admin)->create();
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        foreach ([$admin, $warehouse] as $user) {
            $this->assertTrue($policy->viewAny($user));
            $this->assertTrue($policy->create($user));
            $this->assertTrue($policy->update($user, $category));
            $this->assertTrue($policy->delete($user, $category));
        }
    }

    public function test_policy_denies_cashier_and_manager(): void
    {
        $policy = new CategoryPolicy;
        $category = Category::factory()->create();

        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $manager = User::factory()->withRole(Role::Manager)->create();

        foreach ([$cashier, $manager] as $user) {
            $this->assertFalse($policy->viewAny($user));
            $this->assertFalse($policy->create($user));
            $this->assertFalse($policy->update($user, $category));
            $this->assertFalse($policy->delete($user, $category));
        }
    }

    public function test_warehouse_can_access_category_pages(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('categories.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Index')
                ->where('can.viewAnyCategories', true)
            );

        $this->actingAs($warehouse)
            ->get(route('categories.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Categories/Create')
                ->where('can.viewAnyCategories', true)
            );
    }

    public function test_cashier_is_forbidden_on_category_pages(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();
        $category = Category::factory()->create();

        $this->actingAs($cashier)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($cashier)->get(route('categories.create'))->assertForbidden();
        $this->actingAs($cashier)->get(route('categories.edit', $category))->assertForbidden();
        $this->actingAs($cashier)->post(route('categories.store'), ['name' => 'X'])->assertForbidden();
        $this->actingAs($cashier)->put(route('categories.update', $category), ['name' => 'Y'])->assertForbidden();
        $this->actingAs($cashier)->post(route('categories.deactivate', $category))->assertForbidden();
    }
}
