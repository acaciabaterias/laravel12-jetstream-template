<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

if (app()->environment(['local', 'testing'])) {
    Route::get('/load/tenant-probe', function () {
        $cliente = request()->attributes->get('cliente');

        return response()->json([
            'tenant_host' => config('database.connections.tenant.host'),
            'cliente_id' => $cliente?->id,
            'subdominio' => $cliente?->subdominio,
        ]);
    })->name('load.tenant-probe');
}

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'filial.isolation',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/integration/backbone', function () {
        abort_unless(auth()->user()->can('view-integration-operations'), 403);

        return view('integration.backbone');
    })->name('integration.backbone');
});
