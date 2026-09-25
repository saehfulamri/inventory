<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Role;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_summary_for_authenticated_user(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        Product::factory()->create(['name' => 'Beras Menipis', 'is_active' => true, 'stock' => 3, 'minimum_stock' => 10]);
        Product::factory()->create(['name' => 'Gula Aman', 'is_active' => true, 'stock' => 100, 'minimum_stock' => 10]);

        Sale::factory()->for($cashier)->completed()->create([
            'sale_date' => now()->toDateString(),
            'grand_total' => 150000,
            'paid_amount' => 150000,
        ]);

        $this->actingAs($cashier)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('summary.today_total', 150000)
                ->where('summary.today_count', 1)
                ->where('summary.active_products', 2)
                ->where('summary.low_stock_count', 1)
                ->has('summary.low_stock', 1)
                ->where('summary.low_stock.0.name', 'Beras Menipis')
                ->has('summary.chart', 7)
            );
    }

    public function test_dashboard_shows_linked_low_stock_for_management_roles(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        Product::factory()->create(['name' => 'Sabun Menipis', 'is_active' => true, 'stock' => 2, 'minimum_stock' => 5]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('summary.low_stock.0.name', 'Sabun Menipis')
                ->where('can.viewAnyProducts', true)
            );
    }

    public function test_dashboard_shows_empty_states_when_no_data(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('summary.today_total', 0)
                ->where('summary.today_count', 0)
                ->where('summary.active_products', 0)
                ->where('summary.low_stock_count', 0)
                ->where('summary.low_stock', [])
            );
    }

    public function test_each_role_can_access_dashboard(): void
    {
        foreach (Role::cases() as $role) {
            $user = User::factory()->withRole($role)->create();

            $this->actingAs($user)->get(route('dashboard'))->assertOk();
        }
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
