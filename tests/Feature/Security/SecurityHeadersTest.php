<?php

namespace Tests\Feature\Security;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_login_page_sends_security_headers(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_authenticated_pages_send_security_headers(): void
    {
        $user = User::factory()->withRole(Role::Admin)->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Content-Security-Policy');
        $response->assertHeader('Permissions-Policy');
    }

    public function test_content_security_policy_is_a_safe_baseline(): void
    {
        $response = $this->get(route('login'));

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotEmpty($csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        // Over HTTP (development) upgrade-insecure-requests tidak dikirim agar
        // aset CSS/JS di localhost tetap termuat.
        $this->assertStringNotContainsString('upgrade-insecure-requests', $csp);
    }

    public function test_https_request_receives_upgrade_insecure_requests(): void
    {
        // Skema https pada URL membuat Symfony menetapkan HTTPS=on;
        // withServerVariables(['HTTPS' => 'on']) tidak cukup karena
        // ditimpa oleh skema URI http (=localhost dari APP_URL).
        $response = $this->get('https://localhost'.route('login'));

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertNotEmpty($csp);
        $this->assertStringContainsString('upgrade-insecure-requests', $csp);
    }
}
