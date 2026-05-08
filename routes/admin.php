<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FilialController;
use App\Http\Middleware\PlatformAdminMiddleware;
use App\Livewire\Admin\PlatformBillingDashboard;
use App\Livewire\TenantForm;
use App\Livewire\TenantManager;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->group(function () {
    Route::middleware('guest:platform')->group(function () {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware(['web', PlatformAdminMiddleware::class])->group(function () {
        Route::get('/painel', DashboardController::class)->name('dashboard');
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::resource('filiais', FilialController::class)
            ->parameters(['filiais' => 'filial'])
            ->except('show');
        Route::get('/clientes', TenantManager::class)->name('clientes.index');
        Route::get('/clientes/novo', TenantForm::class)->name('clientes.create');
        Route::get('/clientes/{tenant}/editar', TenantForm::class)->name('clientes.edit');
        Route::get('/billing', PlatformBillingDashboard::class)->name('billing.index');
    });
});
