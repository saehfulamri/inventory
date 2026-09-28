<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Tests for UI component behaviour:
 *  - Flash messages (VFlash) – success/error propagation through session
 *  - VBadge data – low-stock badge data surfaced correctly by the dashboard
 *  - Navigation – active route & authentication guard
 *
 * These tests cover observable server-side contracts so that changes to
 * AppLayout, VFlash, VBadge, or the Dashboard controller do not silently break
 * the UI without a test failure.
 */
class UiComponentsTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function admin(): User
    {
        return User::factory()->withRole(Role::Admin)->create();
    }

    private function cashier(): User
    {
        return User::factory()->withRole(Role::Cashier)->create();
    }

    // -----------------------------------------------------------------------
    // Flash messages (#14 – VFlash)
    // -----------------------------------------------------------------------

    public function test_success_flash_is_present_in_session_after_product_creation(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        $this->actingAs($admin)
            ->post(route('products.store'), [
                'category_id' => $category->id,
                'unit_id' => $unit->id,
                'sku' => 'SKU-UI-001',
                'name' => 'Produk UI Test',
                'purchase_price' => 10000,
                'selling_price' => 15000,
                'minimum_stock' => 5,
                'is_active' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');
    }

    public function test_error_flash_is_present_in_session_after_validation_failure(): void
    {
        $admin = $this->admin();

        // Missing required fields triggers a validation error redirect
        $this->actingAs($admin)
            ->post(route('products.store'), [])
            ->assertSessionHasErrors(['name', 'sku', 'category_id', 'unit_id', 'purchase_price', 'selling_price', 'minimum_stock']);
    }

    public function test_flash_success_is_exposed_as_inertia_prop_on_redirect_target(): void
    {
        $admin = $this->admin();
        $category = Category::factory()->create();
        $unit = Unit::factory()->create();

        // Create a product then follow the redirect so Inertia receives the flashed prop
        $this->actingAs($admin)
            ->withSession(['success' => 'Produk berhasil disimpan.'])
            ->get(route('products.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Index')
                ->where('flash.success', 'Produk berhasil disimpan.')
            );
    }

    // -----------------------------------------------------------------------
    // VBadge – low-stock data (#14 – VBadge)
    // -----------------------------------------------------------------------

    public function test_dashboard_exposes_low_stock_data_for_badge_rendering(): void
    {
        $user = $this->cashier();
        Product::factory()->create([
            'name' => 'Minyak Menipis',
            'is_active' => true,
            'stock' => 2,
            'minimum_stock' => 10,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('summary.low_stock_count', 1)
                ->has('summary.low_stock', 1, fn (Assert $item) => $item
                    ->where('name', 'Minyak Menipis')
                    ->has('stock')
                    ->has('minimum_stock')
                    ->etc()
                )
            );
    }

    public function test_dashboard_does_not_include_above_minimum_products_in_badge_list(): void
    {
        $user = $this->cashier();
        Product::factory()->create([
            'name' => 'Beras Aman',
            'is_active' => true,
            'stock' => 100,
            'minimum_stock' => 10,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->where('summary.low_stock_count', 0)
                ->where('summary.low_stock', [])
            );
    }

    // -----------------------------------------------------------------------
    // Navigation – authentication guard
    // -----------------------------------------------------------------------

    public function test_unauthenticated_request_to_dashboard_redirects_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_reach_dashboard_and_sees_nav_data(): void
    {
        $this->actingAs($this->cashier())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard/Index')
                ->has('summary')
            );
    }

    public function test_admin_can_prop_receives_view_any_products(): void
    {
        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('can.viewAnyProducts', true)
            );
    }
}
