<?php

namespace App\Http\Middleware;

use App\Models\Cliente;
use App\Services\BillingAccessGuard;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TenantConnectionMiddleware
{
    public function __construct(private BillingAccessGuard $billingAccessGuard) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldBypassTenantResolution($request)) {
            return $next($request);
        }

        $subdominio = $this->getSubdominio($request);

        if (! $subdominio) {
            return $next($request);
        }

        $cliente = Cache::remember("tenant:{$subdominio}", 3600, function () use ($subdominio) {
            return Cliente::where('subdominio', $subdominio)->first();
        });

        if (! $cliente) {
            abort(404, 'Empresa não encontrada');
        }

        if (! in_array($cliente->status, ['trial', 'active'])) {
            abort(402, 'Assinatura inativa ou expirada.');
        }

        if ($cliente->status === 'trial' && $cliente->trial_ends_at && $cliente->trial_ends_at->isPast()) {
            $cliente->update(['status' => 'expired']);
            abort(402, 'Período de trial expirado. Assine um plano para continuar.');
        }

        if ($cliente->status === 'active' && $cliente->subscription_ends_at && $cliente->subscription_ends_at->isPast()) {
            $cliente->update(['status' => 'expired']);
            abort(402, 'Assinatura expirada. Renove para continuar.');
        }

        if ($this->billingAccessGuard->shouldBlockClienteAccess($cliente->id)) {
            return $this->denyOverdueAccess($request);
        }

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
                'port' => config('database.connections.tenant.port', '6543'),
                'database' => config('database.connections.tenant.database', 'postgres'),
                'username' => config('database.connections.tenant.username', 'postgres'),
                'password' => $cliente->supabase_db_password,
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ]);
        }

        if (! app()->environment('testing')) {
            DB::purge('tenant');
            DB::reconnect('tenant');
        }

        $request->attributes->set('cliente', $cliente);

        return $next($request);
    }

    private function shouldBypassTenantResolution(Request $request): bool
    {
        if ($request->is('login', 'register', 'user/profile', 'teams/*', 'webhooks/*', 'api/webhooks/*')) {
            return true;
        }

        if ($request->routeIs('admin.*') || str_starts_with($request->getHost(), 'admin.')) {
            return true;
        }

        if ($request->is('admin/*', 'dashboard') && auth()->check() && auth()->user()->isSuperAdmin()) {
            return true;
        }

        if (auth('platform')->check()) {
            return true;
        }

        return false;
    }

    private function getSubdominio(Request $request): ?string
    {
        $host = $request->getHost();

        if ($host === 'localhost' || $host === '127.0.0.1') {
            return null;
        }

        $parts = explode('.', $host);

        if (count($parts) <= 2) {
            return null;
        }

        $subdominio = $parts[0] ?? null;
        if (in_array($subdominio, ['www', 'app'])) {
            $subdominio = $parts[1] ?? null;
        }

        return $subdominio;
    }

    private function denyOverdueAccess(Request $request): Response
    {
        $message = 'Acesso bloqueado por inadimplência. Regularize sua assinatura para continuar.';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
            ], 402);
        }

        return redirect()
            ->route('login')
            ->with('error', $message);
    }
}
