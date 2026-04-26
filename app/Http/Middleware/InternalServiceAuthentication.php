<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Prometheus\CollectorRegistry;

class InternalServiceAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $registry = CollectorRegistry::getDefault();
            $path = $request->route() ? $request->route()->uri() : $request->path();
            
            $reqCounter = $registry->getOrRegisterCounter(
                'app',
                'internal_requests_total',
                'Total de requisicoes internas recebidas',
                ['method', 'path']
            );
            $reqCounter->inc([$request->method(), $path]);
        } catch (\Exception $e) {}

        $expectedKey = config('services.internal_key');

        if (empty($expectedKey)) {
            $this->recordFailure('missing_server_key');
            return response()->json(['message' => 'Configuração de autenticação interna ausente.'], 500);
        }

        $providedKey = $request->header('X-Internal-Service-Key') ?? $request->bearerToken();

        if (empty($providedKey)) {
            $this->recordFailure('missing_client_key');
            return response()->json(['message' => 'Unauthorized service request.'], 401);
        }

        if (! hash_equals($expectedKey, (string) $providedKey)) {
            $this->recordFailure('invalid_key');
            return response()->json(['message' => 'Unauthorized service request.'], 401);
        }

        return $next($request);
    }

    protected function recordFailure(string $reason): void
    {
        try {
            $registry = CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter(
                'app',
                'internal_auth_failures_total',
                'Total de falhas de autenticação de serviços internos',
                ['reason']
            );
            $counter->inc([$reason]);
        } catch (\Exception $e) {}
    }
}
