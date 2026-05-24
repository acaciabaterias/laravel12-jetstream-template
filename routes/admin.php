<?php

use App\Http\Controllers\Admin\AdvancedRecoveryAutomationInspectionController;
use App\Http\Controllers\Admin\AdvancedWhiteLabelInspectionController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\BackboneMonitoringInspectionController;
use App\Http\Controllers\Admin\CriticalLoadInspectionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExecutiveReportingInspectionController;
use App\Http\Controllers\Admin\FilialController;
use App\Http\Controllers\Admin\PlatformBillingInspectionController;
use App\Http\Controllers\Admin\PlatformCommercialAnalyticsInspectionController;
use App\Http\Controllers\Admin\PlatformCurrencyInspectionController;
use App\Http\Controllers\Admin\PlatformFiscalRuleInspectionController;
use App\Http\Controllers\Admin\PlatformFiscalRuleResolutionController;
use App\Http\Controllers\Admin\PlatformLocalizationInspectionController;
use App\Http\Controllers\Admin\PlatformPaymentsInspectionController;
use App\Http\Controllers\Admin\PlatformRevenueRecoveryInspectionController;
use App\Http\Controllers\Admin\ProductionObservabilityInspectionController;
use App\Http\Middleware\PlatformAdminMiddleware;
use App\Livewire\Admin\AdvancedRecoveryAutomationManager;
use App\Livewire\Admin\AdvancedWhiteLabelDashboard;
use App\Livewire\Admin\BackboneMonitoringDashboard;
use App\Livewire\Admin\CriticalLoadOptimizationDashboard;
use App\Livewire\Admin\ExecutiveReportingDashboard;
use App\Livewire\Admin\PlanCatalogManager;
use App\Livewire\Admin\PlatformBillingDashboard;
use App\Livewire\Admin\PlatformCommercialAnalyticsDashboard;
use App\Livewire\Admin\PlatformCurrencyManager;
use App\Livewire\Admin\PlatformFiscalRuleManager;
use App\Livewire\Admin\PlatformLocalizationManager;
use App\Livewire\Admin\PlatformPaymentsDashboard;
use App\Livewire\Admin\PlatformPaymentsManager;
use App\Livewire\Admin\PlatformRevenueRecoveryDashboard;
use App\Livewire\Admin\PlatformRevenueRecoveryManager;
use App\Livewire\Admin\PlatformSubscriptionManager;
use App\Livewire\Admin\ProductionObservabilityDashboard;
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
        Route::get('/currencies', PlatformCurrencyManager::class)->name('currencies.index');
        Route::get('/currencies/inspection', PlatformCurrencyInspectionController::class)->name('currencies.inspection');
        Route::get('/fiscal-rules', PlatformFiscalRuleManager::class)->name('fiscal-rules.index');
        Route::get('/fiscal-rules/inspection', PlatformFiscalRuleInspectionController::class)->name('fiscal-rules.inspection');
        Route::get('/fiscal-rules/resolve', PlatformFiscalRuleResolutionController::class)->name('fiscal-rules.resolve');
        Route::get('/localization', PlatformLocalizationManager::class)->name('localization.index');
        Route::get('/localization/inspection', PlatformLocalizationInspectionController::class)->name('localization.inspection');
        Route::get('/recovery', PlatformRevenueRecoveryDashboard::class)->name('recovery.index');
        Route::get('/recovery/operacoes', PlatformRevenueRecoveryManager::class)->name('recovery.operations');
        Route::get('/recovery/automation', AdvancedRecoveryAutomationManager::class)->name('recovery.automation');
        Route::get('/recovery/automation/inspection', AdvancedRecoveryAutomationInspectionController::class)->name('recovery.automation.inspection');
        Route::get('/recovery/inspection', PlatformRevenueRecoveryInspectionController::class)->name('recovery.inspection');
        Route::get('/analytics', PlatformCommercialAnalyticsDashboard::class)->name('analytics.index');
        Route::get('/analytics/inspection', PlatformCommercialAnalyticsInspectionController::class)->name('analytics.inspection');
        Route::get('/reports', ExecutiveReportingDashboard::class)->name('reports.index');
        Route::get('/reports/inspection', ExecutiveReportingInspectionController::class)->name('reports.inspection');
        Route::get('/operations', ProductionObservabilityDashboard::class)->name('operations.index');
        Route::get('/operations/inspection', ProductionObservabilityInspectionController::class)->name('operations.inspection');
        Route::get('/monitoring', BackboneMonitoringDashboard::class)->name('monitoring.index');
        Route::get('/monitoring/inspection', BackboneMonitoringInspectionController::class)->name('monitoring.inspection');
        Route::get('/capacity', CriticalLoadOptimizationDashboard::class)->name('capacity.index');
        Route::get('/capacity/inspection', CriticalLoadInspectionController::class)->name('capacity.inspection');
        Route::get('/branding', AdvancedWhiteLabelDashboard::class)->name('branding.index');
        Route::get('/branding/inspection', AdvancedWhiteLabelInspectionController::class)->name('branding.inspection');
    });
});
