<?php

namespace App\Http\Middleware;

use App\Models\Cliente;
use Closure;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantConnectionMiddleware
{
    public function handle($request, Closure $next)
    {
        // Pular rotas públicas e administrativas centrais
        if ($request->is('login', 'register', 'user/profile', 'teams/*', 'webhooks/*', 'api/webhooks/*')) {
            return $next($request);
        }

        // Para super_admin (acesso via dashboard central)
        if ($request->is('admin/*', 'dashboard') && auth()->check() && auth()->user()->isSuperAdmin()) {
            return $next($request);
        }

        $subdominio = $this->getSubdominio($request);

        // Se não houver subdomínio identificado (ex: domínio principal ou localhost), 
        // permitimos que a requisição siga para a aplicação central.
        if (! $subdominio) {
            return $next($request);
        }

        // Busca cliente no banco central
        $cliente = Cliente::where('subdominio', $subdominio)->first();

        if (! $cliente) {
            abort(404, 'Empresa não encontrada');
        }

        // Verifica status e trial/assinatura
        if (! in_array($cliente->status, ['trial', 'active'])) {
            abort(402, 'Assinatura inativa ou expirada.');
        }

        // Verifica trial expirado
        if ($cliente->status === 'trial' && $cliente->trial_ends_at && $cliente->trial_ends_at->isPast()) {
            $cliente->update(['status' => 'expired']);
            abort(402, 'Período de trial expirado. Assine um plano para continuar.');
        }

        // Verifica assinatura expirada
        if ($cliente->status === 'active' && $cliente->subscription_ends_at && $cliente->subscription_ends_at->isPast()) {
            $cliente->update(['status' => 'expired']);
            abort(402, 'Assinatura expirada. Renove para continuar.');
        }

        // Configura conexão dinâmica com o banco do cliente
        if (app()->environment('testing')) {
            Config::set('database.connections.tenant', [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'host' => $cliente->supabase_db_host,
                'password' => $cliente->supabase_db_password,
            ]);
        } else {
            Config::set('database.connections.tenant', [
                'driver' => 'pgsql',
                'host' => $cliente->supabase_db_host,
                'port' => env('DB_TENANT_PORT', '6543'),
                'database' => 'postgres',
                'username' => 'postgres',
                'password' => $cliente->supabase_db_password,
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ]);
        }

        // Purge e reconecta para garantir que as queries seguintes usem a nova config
        // Purge e reconecta para garantir que as queries seguintes usem a nova config
        if (! app()->environment('testing')) {
            DB::purge('tenant');
            DB::reconnect('tenant');
        }

        // Compartilha cliente com a requisição via atributo
        $request->attributes->set('cliente', $cliente);

        return $next($request);
    }

    private function getSubdominio($request): ?string
    {
        $host = $request->getHost();
        
        // Se for localhost ou o domínio base puro, não há subdominio de tenant
        if ($host === 'localhost' || $host === '127.0.0.1') {
            return null;
        }

        $parts = explode('.', $host);

        // Se o domínio for 'erp.com' e tiver apenas 2 partes, não há subdomínio
        if (count($parts) <= 2) {
            return null;
        }

        // Remove www e app se existirem para pegar o identificador do tenant
        $subdominio = $parts[0] ?? null;
        if (in_array($subdominio, ['www', 'app'])) {
            $subdominio = $parts[1] ?? null;
        }

        return $subdominio;
    }
}
