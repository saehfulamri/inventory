<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAndFlashTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the dashboard page loads successfully for an authenticated user.
     */
    public function test_dashboard_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
    }
}
