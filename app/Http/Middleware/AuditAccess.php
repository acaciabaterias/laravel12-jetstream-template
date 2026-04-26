<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $label = null): Response
    {
        $response = $next($request);

        // Somente loga se for bem-sucedido e houver usuário autenticado
        if ($response->isSuccessful() && Auth::check()) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'access',
                'table_name' => $label ?? 'route',
                'record_id' => 0,
                'old_values' => null,
                'new_values' => [
                    'path' => $request->path(),
                    'method' => $request->method(),
                    'full_url' => $request->fullUrl(),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }
}
