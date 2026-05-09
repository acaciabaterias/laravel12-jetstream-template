<?php

use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FilialController;
use App\Http\Controllers\Admin\PlatformBillingInspectionController;
use App\Http\Controllers\Admin\PlatformCommercialAnalyticsInspectionController;
use App\Http\Controllers\Admin\PlatformPaymentsInspectionController;
use App\Http\Controllers\Admin\PlatformRevenueRecoveryInspectionController;
use App\Http\Middleware\PlatformAdminMiddleware;
use App\Livewire\Admin\PlanCatalogManager;
use App\Livewire\Admin\PlatformBillingDashboard;
use App\Livewire\Admin\PlatformCommercialAnalyticsDashboard;
use App\Livewire\Admin\PlatformPaymentsDashboard;
use App\Livewire\Admin\PlatformPaymentsManager;
use App\Livewire\Admin\PlatformRevenueRecoveryDashboard;
use App\Livewire\Admin\PlatformRevenueRecoveryManager;
use App\Livewire\Admin\PlatformSubscriptionManager;
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
        Route::get('/billing/planos', PlanCatalogManager::class)->name('billing.plans');
        Route::get('/billing/assinaturas', PlatformSubscriptionManager::class)->name('billing.subscriptions');
        Route::get('/billing/inspection', PlatformBillingInspectionController::class)->name('billing.inspection');
        Route::get('/payments', PlatformPaymentsDashboard::class)->name('payments.index');
        Route::get('/payments/emitir', PlatformPaymentsManager::class)->name('payments.issue');
        Route::get('/payments/inspection', PlatformPaymentsInspectionController::class)->name('payments.inspection');
        Route::get('/recovery', PlatformRevenueRecoveryDashboard::class)->name('recovery.index');
        Route::get('/recovery/operacoes', PlatformRevenueRecoveryManager::class)->name('recovery.operations');
        Route::get('/recovery/inspection', PlatformRevenueRecoveryInspectionController::class)->name('recovery.inspection');
        Route::get('/analytics', PlatformCommercialAnalyticsDashboard::class)->name('analytics.index');
        Route::get('/analytics/inspection', PlatformCommercialAnalyticsInspectionController::class)->name('analytics.inspection');
    });
});
