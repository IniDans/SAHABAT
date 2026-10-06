<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Header keamanan untuk semua respons: Content-Security-Policy, anti-clickjacking,
 * anti-MIME-sniffing, HSTS (khusus HTTPS), dan pembatasan fitur browser.
 */
class HeaderKeamanan
{
    /**
     * Sumber luar yang boleh tampil di iframe (peta lokasi di halaman Kontak).
     */
    private const FRAME_SRC = ['https://maps.google.com', 'https://www.google.com'];

    public function handle(Request $request, Closure $next): Response
    {
        // Script dari Vite diberi nonce, jadi script inline sisipan penyerang tidak akan jalan.
        Vite::useCspNonce();

        $response = $next($request);

        header_remove('X-Powered-By');
        $response->headers->remove('X-Powered-By');

        $response->headers->add([
            'Content-Security-Policy' => $this->csp($request),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
        ]);

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    private function csp(Request $request): string
    {
        $vite = Vite::isRunningHot() ? $this->viteDevServer() : [];
        $viteWs = array_map(fn (string $origin): string => preg_replace('#^http#', 'ws', $origin), $vite);

        $arahan = [
            'default-src' => ["'self'"],
            'script-src' => ["'self'", "'nonce-".Vite::cspNonce()."'", ...$vite],
            // Tailwind memakai beberapa atribut style inline (lebar progress bar, grafik).
            'style-src' => ["'self'", "'unsafe-inline'", ...$vite],
            'img-src' => ["'self'", 'data:', 'blob:', 'https:'],
            'font-src' => ["'self'", 'data:', ...$vite],
            'connect-src' => ["'self'", ...$vite, ...$viteWs],
            'frame-src' => self::FRAME_SRC,
            'frame-ancestors' => ["'self'"],
            'form-action' => ["'self'"],
            'base-uri' => ["'self'"],
            'object-src' => ["'none'"],
        ];

        $kebijakan = collect($arahan)->map(fn (array $sumber, string $nama): string => $nama.' '.implode(' ', $sumber))->values();

        if ($request->isSecure()) {
            $kebijakan->push('upgrade-insecure-requests');
        }

        return $kebijakan->implode('; ');
    }

    /**
     * Origin dev server Vite (npm run dev), dibaca dari berkas public/hot.
     *
     * @return list<string>
     */
    private function viteDevServer(): array
    {
        $url = trim((string) @file_get_contents(Vite::hotFile()));
        $origin = parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).(parse_url($url, PHP_URL_PORT) ? ':'.parse_url($url, PHP_URL_PORT) : '');

        return $url === '' ? [] : [$origin];
    }
}
