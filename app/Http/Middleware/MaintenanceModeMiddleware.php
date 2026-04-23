<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia requisicoes quando o modo de manutencao customizado estiver ativo.
 */
class MaintenanceModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = (bool) config('services.platform.maintenance_mode', false);
        $allowedIps = array_filter((array) config('services.platform.maintenance_allowed_ips', []));

        if ($enabled && ! in_array((string) $request->ip(), $allowedIps, true)) {
            abort(503, 'Aplicacao em manutencao.');
        }

        return $next($request);
    }
}
