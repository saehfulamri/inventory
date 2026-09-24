<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Role;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSee('Penjualan Hari Ini')
            ->assertSee('150.000')
            ->assertSee('Produk Stok Menipis')
            ->assertSee('Beras Menipis')
            ->assertSee('Penjualan 7 Hari Terakhir');
    }

    public function test_dashboard_shows_linked_low_stock_for_management_roles(): void
    {
        $admin = User::factory()->withRole(Role::Admin)->create();
        Product::factory()->create(['name' => 'Sabun Menipis', 'is_active' => true, 'stock' => 2, 'minimum_stock' => 5]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Sabun Menipis')
            ->assertSee('Lihat Semua');
    }

    public function test_dashboard_shows_empty_states_when_no_data(): void
    {
        $warehouse = User::factory()->withRole(Role::Warehouse)->create();

        $this->actingAs($warehouse)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Rp 0')
            ->assertSee('Semua produk berada di atas stok minimum.');
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
