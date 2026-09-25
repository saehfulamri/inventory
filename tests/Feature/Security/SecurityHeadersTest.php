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
        // Harden: tidak ada 'unsafe-inline' pada script-src — skrip inline
        // data-block Inertia diizinkan lewat nonce per-respons.
        $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
        // Over HTTP (development) upgrade-insecure-requests tidak dikirim agar
        // aset CSS/JS di localhost tetap termuat.
        $this->assertStringNotContainsString('upgrade-insecure-requests', $csp);
    }

    public function test_inertia_page_data_script_receives_matching_csp_nonce(): void
    {
        $response = $this->get(route('login'));

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression(
            "/script-src 'self' 'nonce-([A-Za-z0-9+\/=]+)'/",
            $csp,
            'CSP script-src wajib memuat nonce per-respons.'
        );

        preg_match("/script-src 'self' 'nonce-([A-Za-z0-9+\/=]+)'/", $csp, $matches);

        // Tanpa nonce pada data-block Inertia, script-src memblokirnya dan
        // halaman gagal di-bootstrap (layar beku) — ini guard regresi utama.
        $this->assertStringContainsString(
            '<script data-page="app" type="application/json" nonce="'.$matches[1].'"',
            $response->getContent()
        );
    }

    public function test_vite_dev_server_origin_is_allowed_when_hot_file_exists(): void
    {
        $hotFile = public_path('hot');
        $original = is_file($hotFile) ? file_get_contents($hotFile) : false;

        file_put_contents($hotFile, 'http://127.0.0.1:5173');

        try {
            $response = $this->get(route('login'));

            $csp = $response->headers->get('Content-Security-Policy');

            // Saat `npm run dev` aktif, aset & HMR dilayani dari origin Vite
            // dev server — tanpa izin ini seluruh script diblokir (layar putih).
            $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
            $this->assertStringContainsString('http://127.0.0.1:5173', $csp);
            // WebSocket HMR dev server.
            $this->assertStringContainsString('ws://127.0.0.1:5173', $csp);
            // Tetap tanpa 'unsafe-inline' pada script-src di mode dev.
            $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
        } finally {
            if ($original !== false) {
                file_put_contents($hotFile, $original);
            } else {
                @unlink($hotFile);
            }
        }
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
