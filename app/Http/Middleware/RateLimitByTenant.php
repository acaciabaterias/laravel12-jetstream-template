<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use OpenApi\Attributes as OA;
use Prometheus\CollectorRegistry;
use Symfony\Component\HttpFoundation\Response;

#[OA\Schema(
    schema: 'RateLimitHeaders',
    properties: [
        new OA\Property(property: 'X-RateLimit-Limit', type: 'integer', description: 'Limite total permitido'),
        new OA\Property(property: 'X-RateLimit-Remaining', type: 'integer', description: 'Requisições restantes'),
        new OA\Property(property: 'X-RateLimit-Reset', type: 'integer', description: 'Timestamp Unix de reset'),
    ]
)]
class RateLimitByTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cliente = $request->attributes->get('cliente');

        // Se não houver cliente identificado (ex: rotas globais), pula o rate limit
        if (! $cliente) {
            return $next($request);
        }

        $limit = $cliente->getRateLimitPerMinute();
        $path = $request->route() ? $request->route()->uri() : $request->path();

        // Chave única por tenant, IP e endpoint
        $key = "tenant:{$cliente->id}:{$request->ip()}:".$path;

        if (RateLimiter::tooManyAttempts($key, $limit)) {
            $this->incrementPrometheusMetric($cliente, $path);

            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too Many Requests',
                'retry_after' => $seconds,
            ], 429)
                ->header('Retry-After', $seconds)
                ->header('X-RateLimit-Limit', $limit)
                ->header('X-RateLimit-Remaining', 0)
                ->header('X-RateLimit-Reset', now()->addSeconds($seconds)->timestamp);
        }

        RateLimiter::hit($key, 60); // Expira em 1 minuto
        $this->incrementPrometheusMetric($cliente, $path);

        $response = $next($request);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => RateLimiter::remaining($key, $limit),
            'X-RateLimit-Reset' => now()->addSeconds(RateLimiter::availableIn($key))->timestamp,
        ]);
    }

    /**
     * Incrementa a métrica de hits do Rate Limit no Prometheus.
     */
    private function incrementPrometheusMetric($cliente, string $path): void
    {
        try {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter(
                'app',
                'rate_limit_hits_total',
                'Total de rate limit hits',
                ['tenant_id', 'plan', 'endpoint']
            );

            $counter->inc([
                (string) $cliente->id,
                (string) ($cliente->plano ?? 'free'),
                $path,
            ]);
        } catch (\Throwable $e) {
            // Silently ignore prometheus errors in middleware
        }
    }
}
