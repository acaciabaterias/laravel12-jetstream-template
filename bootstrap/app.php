<?php

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
            'tenant' => \App\Http\Middleware\TenantConnectionMiddleware::class,
            'filial.isolation' => \App\Http\Middleware\FilialIsolation::class,
            'cors.custom' => \App\Http\Middleware\CorsMiddleware::class,
            'security.headers' => \App\Http\Middleware\SecurityHeadersMiddleware::class,
            'rate.role' => \App\Http\Middleware\RateLimitByRoleMiddleware::class,
            'audit.requests' => \App\Http\Middleware\AuditMiddleware::class,
            'maintenance.allowlist' => \App\Http\Middleware\MaintenanceModeMiddleware::class,
            'internal.auth' => \App\Http\Middleware\InternalServiceAuthentication::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\TenantConnectionMiddleware::class,
            \App\Http\Middleware\PrometheusMetrics::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\PrometheusMetrics::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
