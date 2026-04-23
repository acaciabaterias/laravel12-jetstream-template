<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FilialController;
use App\Livewire\TenantForm;
use App\Livewire\TenantManager;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->group(function () {
    // Rotas Públicas (Login)
    Route::get('/login', function () {
        return 'Admin Login Page (Coming Soon)';
    })->name('login');

    // Rotas Protegidas
    Route::middleware(['web', \App\Http\Middleware\PlatformAdminMiddleware::class])->group(function () {
        Route::get('/painel', DashboardController::class)->name('dashboard');
        Route::resource('filiais', FilialController::class)
            ->parameters(['filiais' => 'filial'])
            ->except('show');
        Route::get('/clientes', TenantManager::class)->name('clientes.index');
        Route::get('/clientes/novo', TenantForm::class)->name('clientes.create');
        Route::get('/clientes/{tenant}/editar', TenantForm::class)->name('clientes.edit');
    });
});
