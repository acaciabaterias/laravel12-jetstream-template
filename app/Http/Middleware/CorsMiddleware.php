<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica cabecalhos CORS customizados por configuracao.
 */
class CorsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $origins = (array) config('services.platform.cors_allowed_origins', ['*']);
        $methods = (array) config('services.platform.cors_allowed_methods', ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']);
        $origin = $request->headers->get('Origin', '*');

        if (in_array('*', $origins, true) || in_array($origin, $origins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', in_array('*', $origins, true) ? '*' : $origin);
        }

        $response->headers->set('Access-Control-Allow-Methods', implode(', ', $methods));
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}
