<?php

use App\Http\Middleware\AuditAccess;
use App\Http\Middleware\AuditMiddleware;
use App\Http\Middleware\CorsMiddleware;
use App\Http\Middleware\FilialIsolation;
use App\Http\Middleware\InternalServiceAuthentication;
use App\Http\Middleware\MaintenanceModeMiddleware;
use App\Http\Middleware\PrometheusMetrics;
use App\Http\Middleware\RateLimitByRoleMiddleware;
use App\Http\Middleware\RateLimitByTenant;
use App\Http\Middleware\ResolvePlatformLocale;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Http\Middleware\TenantConnectionMiddleware;
use App\Http\Middleware\VerifyHmacSignature;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            $adminHost = parse_url(config('app.url'), PHP_URL_HOST);

            if ($adminHost) {
                $adminRoutes = fn () => Route::group(base_path('routes/admin.php'));
                $localHosts = ['localhost', '127.0.0.1'];

                if (in_array($adminHost, $localHosts, true) || str_ends_with($adminHost, '.localhost')) {
                    Route::middleware('web')
                        ->prefix('admin')
                        ->group(base_path('routes/admin.php'));
                } else {
                    Route::middleware('web')
                        ->domain('admin.'.$adminHost)
                        ->group(base_path('routes/admin.php'));
                }
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant' => TenantConnectionMiddleware::class,
            'filial.isolation' => FilialIsolation::class,
            'cors.custom' => CorsMiddleware::class,
            'security.headers' => SecurityHeadersMiddleware::class,
            'rate.role' => RateLimitByRoleMiddleware::class,
            'audit.requests' => AuditMiddleware::class,
            'maintenance.allowlist' => MaintenanceModeMiddleware::class,
            'internal.auth' => InternalServiceAuthentication::class,
            'rate_limit.tenant' => RateLimitByTenant::class,
            'internal.hmac' => VerifyHmacSignature::class,
            'audit' => AuditAccess::class,
        ]);

        $middleware->web(append: [
            TenantConnectionMiddleware::class,
            ResolvePlatformLocale::class,
            PrometheusMetrics::class,
        ]);

        $middleware->api(append: [
            TenantConnectionMiddleware::class,
            PrometheusMetrics::class,
            RateLimitByTenant::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
