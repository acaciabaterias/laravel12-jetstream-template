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
        // Pular rotas públicas e webhooks (opcional, dependendo do design)
        if ($request->is('login', 'register', 'webhooks/*', 'api/webhooks/*')) {
            return $next($request);
        }

        // Para super_admin (acesso via dashboard central)
        if ($request->is('admin/*') && auth()->user() instanceof \App\Models\UsuarioPlataforma) {
            return $next($request);
        }

        $subdominio = $this->getSubdominio($request);

        if (! $subdominio) {
            abort(400, 'Subdomínio não identificado');
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
                'database' => env('DB_TENANT_DATABASE', database_path('test_tenant.sqlite')),
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
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Compartilha cliente com a requisição via atributo
        $request->attributes->set('cliente', $cliente);

        return $next($request);
    }

    private function getSubdominio($request): ?string
    {
        $host = $request->getHost();
        $parts = explode('.', $host);

        // Remove www e app se existirem para pegar o identificador do tenant
        $subdominio = $parts[0] ?? null;
        if (in_array($subdominio, ['www', 'app'])) {
            $subdominio = $parts[1] ?? null;
        }

        return $subdominio;
    }
}
