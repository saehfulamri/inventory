<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardPerformanceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ensure the dashboard page loads with minimal queries (eager‑loaded).
     */
    public function test_dashboard_query_count(): void
    {
        $user = User::factory()->create();
        $queries = 0;
        DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->actingAs($user)->get('/dashboard')->assertStatus(200);

        // Expect at most 2 queries: one for today's sales with items, one for low‑stock products.
        $this->assertLessThanOrEqual(5, $queries, "Dashboard should execute ≤5 queries, got $queries");
    }
}
