<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Nonce CSP per-respons. Inertia v3 merender data halaman awal sebagai
        // data-block <script type="application/json"> yang ikut tunduk ke
        // script-src: tanpa 'unsafe-inline' ia diblokir browser dan halaman
        // gagal di-bootstrap. Nonce dibagikan ke view (dipakai direktif
        // @inertia) dan dipakai pada header CSP agar nilainya sama.
        $nonce = base64_encode(random_bytes(18));

        View::share('cspNonce', $nonce);

        $response = $next($request);

        // Security headers
        if (! $response->headers->has('X-Content-Type-Options')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
        }

        if (! $response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }

        if (! $response->headers->has('Referrer-Policy')) {
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        }

        if (! $response->headers->has('Permissions-Policy')) {
            $response->headers->set(
                'Permissions-Policy',
                'geolocation=(), microphone=(), camera=(), fullscreen=(self)'
            );
        }

        // Baseline CSP. Seluruh aset aplikasi dimuat via Vite (script
        // type=module) dan konfigurasi rute Ziggy dipindahkan ke modul JS
        // statis, sehingga tidak ada script inline aplikasi. Data halaman
        // Inertia dirender sebagai data-block <script type="application/json">
        // yang diizinkan lewat nonce per-respons (defense-in-depth — sebagian
        // browser tetap memperlakukannya sebagai subjek script-src).
        if (! $response->headers->has('Content-Security-Policy')) {
            // Saat `npm run dev` aktif, Vite menulis public/hot dan aset serta
            // HMR dilayani dari origin dev (default http://127.0.0.1:5173).
            // Origin itu HARUS diizinkan, jika tidak seluruh script diblokir
            // CSP dan halaman tampak putih/beku di mode development.
            $devServer = $this->viteDevServerUrl();

            $scriptSrc = "'self' 'nonce-{$nonce}'";
            $styleSrc = "'self' 'unsafe-inline'";
            $imgSrc = "'self' data:";
            $fontSrc = "'self' data:";
            $connectSrc = "'self'";

            if ($devServer !== null) {
                $wsDevServer = preg_replace('#^http://#', 'ws://', $devServer);

                $scriptSrc .= " {$devServer}";
                $styleSrc .= " {$devServer}";
                $imgSrc .= " {$devServer}";
                $fontSrc .= " {$devServer}";
                $connectSrc .= " {$devServer} {$wsDevServer}";
            }

            $csp = [
                "default-src 'self'",
                "script-src {$scriptSrc}",
                "style-src {$styleSrc}",
                "img-src {$imgSrc}",
                "font-src {$fontSrc}",
                "connect-src {$connectSrc}",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
            ];

            // Hanya untuk request HTTPS: upgrade-insecure-requests pada halaman
            // HTTP (mis. development via php artisan serve) akan merusak aset
            // karena browser mencoba memuat CSS/JS lewat HTTPS.
            if ($request->isSecure()) {
                array_unshift($csp, 'upgrade-insecure-requests');
            }

            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }

    /**
     * URL origin Vite dev server saat `npm run dev` berjalan, atau null.
     *
     * Vite menulis file public/hot dengan URL server-nya selama dev server
     * aktif (mis. http://127.0.0.1:5173). Deteksi ini mengikuti perilaku
     * direktif @vite Laravel sehingga CSP selalu sejalan dengan origin
     * yang benar-benar dipakai untuk melayani aset.
     */
    private function viteDevServerUrl(): ?string
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));

        return $url === '' ? null : $url;
    }
}
