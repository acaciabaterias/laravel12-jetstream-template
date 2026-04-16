<?php

namespace App\Http\Middleware;

use App\Models\Filial;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantResolver
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // Pular para rotas públicas (login, register, webhooks)
        if ($request->is('login', 'register', 'webhooks/*')) {
            return $next($request);
        }
        
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdominio = $parts[0] ?? null;
        
        // Se tem subdomínio válido (ignora www e app)
        if ($subdominio && !in_array($subdominio, ['www', 'app'])) {
            $filial = Filial::where('subdominio', $subdominio)
                ->orWhere('dominio_personalizado', $host)
                ->first();
            
            if (!$filial) {
                abort(404, 'Empresa não encontrada');
            }
            
            // Verifica assinatura
            if (!$filial->hasActiveSubscription() && !$filial->withinTrial()) {
                abort(402, 'Assinatura expirada. Acesse o portal para renovar.');
            }
            
            $request->attributes->set('tenant', $filial);
            session(['filial_id' => $filial->id]);
        }
        
        return $next($request);
    }
}
