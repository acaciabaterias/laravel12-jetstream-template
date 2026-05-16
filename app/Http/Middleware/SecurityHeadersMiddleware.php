<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Injeta cabecalhos de seguranca nas respostas HTTP.
 */
class SecurityHeadersMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('Strict-Transport-Security', (string) config('services.platform.hsts', 'max-age=31536000; includeSubDomains'));
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Content-Security-Policy', (string) config('services.platform.csp', "default-src 'self'; frame-ancestors 'self'; base-uri 'self';"));

        return $response;
    }
}
