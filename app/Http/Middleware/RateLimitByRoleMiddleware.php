<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Limita requisicoes por papel do usuario autenticado.
 */
class RateLimitByRoleMiddleware
{
    public function __construct(private readonly RateLimiter $rateLimiter) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $maxAttempts = match (true) {
            $user?->isSuperAdmin() => 1000,
            $user?->hasRole(['dono', 'gestor']) => 500,
            $user?->hasRole('vendedor') => 300,
            $user?->hasRole('entregador') => 100,
            default => 120,
        };

        $key = sprintf('role-rate:%s:%s', $user?->id ?? 'guest', sha1($request->ip().$request->path()));

        if ($this->rateLimiter->tooManyAttempts($key, $maxAttempts)) {
            abort(429, 'Limite de requisicoes excedido para o papel atual.');
        }

        $this->rateLimiter->hit($key, 60);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) max($maxAttempts - $this->rateLimiter->attempts($key), 0));

        return $response;
    }
}
