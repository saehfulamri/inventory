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
        // yang diizinkan lewat nonce per-respons.
        if (! $response->headers->has('Content-Security-Policy')) {
            // Saat `npm run dev` aktif, Vite menulis public/hot dan aset serta
            // HMR dilayani dari origin dev. Origin itu HARUS diizinkan, jika tidak
            // seluruh script diblokir CSP.
            $devServerOrigin = $this->viteDevServerOrigin();

            $scriptSrc = "'self' 'nonce-{$nonce}'";
            $styleSrc = "'self' 'unsafe-inline'";
            $imgSrc = "'self' data:";
            $fontSrc = "'self' data:";
            $connectSrc = "'self'";

            if ($devServerOrigin !== null) {
                $wsDevServerOrigin = preg_replace('#^http://#', 'ws://', $devServerOrigin);
                $wssDevServerOrigin = preg_replace('#^https://#', 'wss://', $devServerOrigin);

                $scriptSrc .= " {$devServerOrigin}";
                $styleSrc .= " {$devServerOrigin}";
                $imgSrc .= " {$devServerOrigin}";
                $fontSrc .= " {$devServerOrigin}";
                $connectSrc .= " {$devServerOrigin} {$wsDevServerOrigin} {$wssDevServerOrigin}";

                // Vite dev server/HMR dapat menyuntikkan inline script pada development.
                // Tetap pertahankan nonce untuk data-block Inertia dan script aplikasi.
                if (app()->environment('local')) {
                    $scriptSrc .= " 'unsafe-inline'";
                }
            }

            $scriptSrcElem = $scriptSrc;
            if ($devServerOrigin !== null && app()->environment('local')) {
                $scriptSrcElem .= " 'unsafe-inline'";
            }

            $csp = [
                "default-src 'self'",
                "script-src {$scriptSrc}",
                "script-src-elem {$scriptSrcElem}",
                "style-src {$styleSrc}",
                "style-src-elem {$styleSrc}",
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
     * Origin Vite dev server yang dikanonisasi untuk CSP.
     * Baca dari public/hot, paksa ke 127.0.0.1 untuk kompatibilitas dengan
     * browser (beberapa browser menolak bentuk [::1] sebagai source di CSP).
     */
    private function viteDevServerOrigin(): ?string
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return null;
        }

        $url = trim((string) file_get_contents($hotFile));

        if ($url === '') {
            return null;
        }

        $parsed = parse_url($url);

        if (! is_array($parsed) || ! isset($parsed['scheme'], $parsed['port'])) {
            return null;
        }

        $scheme = $parsed['scheme'];
        $port = $parsed['port'];

        // Kanonisasi ke 127.0.0.1 (kompatibel dengan CSP dan konsisten dengan vite.config)
        return $scheme.'://127.0.0.1:'.$port;
    }
}
