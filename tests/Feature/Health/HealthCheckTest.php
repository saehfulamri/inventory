<?php

namespace Tests\Feature\Health;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_is_public_and_reports_all_checks_ok(): void
    {
        $response = $this->get(route('health'));

        $response->assertOk();
        $response->assertJson([
            'status' => 'ok',
            'checks' => [
                'database' => true,
                'cache' => true,
                'queue' => true,
            ],
        ]);
    }

    public function test_health_endpoint_returns_503_when_a_check_fails(): void
    {
        config(['queue.default' => 'tidak-ada-driver']);

        $response = $this->get(route('health'));

        $response->assertStatus(503);
        $response->assertJson([
            'status' => 'error',
            'checks' => [
                'database' => true,
                'cache' => true,
                'queue' => false,
            ],
        ]);
    }

    public function test_health_endpoint_does_not_require_authentication_or_create_session(): void
    {
        $response = $this->get(route('health'));

        $response->assertOk();
        $this->assertGuest();
        // Health check sengaja berada di luar grup `web` agar probe monitoring
        // tidak membuat/menulis sesi maupun cookie.
        $this->assertNull($response->headers->get('Set-Cookie'));
    }
}
