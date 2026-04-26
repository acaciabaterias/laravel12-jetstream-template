<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Prometheus\CollectorRegistry;

class PrometheusMetrics
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $durationMs = (microtime(true) - $start) * 1000;

        $path = $request->route() ? $request->route()->uri() : $request->path();

        try {
            $registry = CollectorRegistry::getDefault();
            
            $counter = $registry->getOrRegisterCounter(
                'app',
                'http_requests_total',
                'Total de requisições HTTP',
                ['method', 'path', 'status']
            );
            $counter->inc([$request->method(), $path, (string) $response->status()]);

            $histogram = $registry->getOrRegisterHistogram(
                'app',
                'http_request_duration_ms',
                'Duração da requisição em ms',
                ['path']
            );
            $histogram->observe($durationMs, [$path]);
        } catch (\Exception $e) {
            // Silently ignore prometheus errors in middleware
        }

        return $response;
    }
}
