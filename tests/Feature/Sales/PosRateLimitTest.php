<?php

namespace Tests\Feature\Sales;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_endpoint_is_rate_limited_per_user(): void
    {
        $cashier = User::factory()->withRole(Role::Cashier)->create();

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($cashier)->get(route('sales.products'))->assertOk();
        }

        $this->actingAs($cashier)->get(route('sales.products'))->assertStatus(429);
    }

    public function test_product_search_rate_limit_is_scoped_per_user(): void
    {
        $first = User::factory()->withRole(Role::Cashier)->create();
        $second = User::factory()->withRole(Role::Cashier)->create();

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($first)->get(route('sales.products'))->assertOk();
        }

        $this->actingAs($first)->get(route('sales.products'))->assertStatus(429);
        $this->actingAs($second)->get(route('sales.products'))->assertOk();
    }
}
