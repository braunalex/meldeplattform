<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), interest-cohort=()',
        );

        // Content-Security-Policy tuned to our actual asset surface:
        //   - self for static CSS/JS/fonts we ship under /css, /js
        //   - Google Fonts (fonts.googleapis.com + fonts.gstatic.com) for the
        //     Source Sans 3 webfont used by the TUM-style theme
        //   - script-src is strict: no 'unsafe-inline', no 'unsafe-eval'. Page
        //     data is passed to external JS through non-executable
        //     <script type="application/json"> data islands, which browsers
        //     never parse as JavaScript.
        //   - 'unsafe-inline' remains in style-src for the handful of inline
        //     style attributes in Blade templates.
        $response->headers->set('Content-Security-Policy', implode('; ', [
            "default-src 'self'",
            "img-src 'self' data:",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com",
            "script-src 'self'",
            "object-src 'none'",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        // HSTS only over a trusted TLS terminator. Laravel's `secure()` check
        // honours trusted proxies, so this fires behind Traefik/nginx SSL too.
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains',
            );
        }

        return $response;
    }
}
