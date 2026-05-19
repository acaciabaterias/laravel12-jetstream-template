<?php

namespace App\Providers;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\AlertRuleDefinition;
use App\Models\AssinaturaPlataforma;
use App\Models\BenchmarkExecutionRecord;
use App\Models\BoletoOrquestrado;
use App\Models\BrandIdentityProfile;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\CnabRemessa;
use App\Models\CnabRetornoUpload;
use App\Models\CobrancaSaaSExterna;
use App\Models\CompromissoPagamento;
use App\Models\ConciliacaoPagamentoSaaS;
use App\Models\ContaBancaria;
use App\Models\DashboardProvisioningRecord;
use App\Models\Deposito;
use App\Models\DrilldownAnalyticsComercial;
use App\Models\EntregaIntegracao;
use App\Models\EstoqueMovimentacao;
use App\Models\EventoComercialAssinante;
use App\Models\ExcecaoConciliacaoSaaS;
use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\ExecutiveReportDefinition;
use App\Models\ExecutiveReportExecutionLog;
use App\Models\ExecutiveReportExport;
use App\Models\FaturaSaaS;
use App\Models\FilaContingencia;
use App\Models\GatewayCobrancaSaaS;
use App\Models\IndicadorRecuperacaoReceita;
use App\Models\InsightRiscoComercial;
use App\Models\LoadScenarioProfile;
use App\Models\LoadTestBaseline;
use App\Models\MetricaPerformanceCanal;
use App\Models\MonitoringProbeSnapshot;
use App\Models\MonitoringReadinessEvidence;
use App\Models\MonitoringTargetCatalog;
use App\Models\NotaFiscalOrquestrada;
use App\Models\OperationalAlertSnapshot;
use App\Models\OperationalIncidentRecord;
use App\Models\OperationalSloDefinition;
use App\Models\OrdemServico;
use App\Models\OrdemServicoGarantia;
use App\Models\PedidoVenda;
use App\Models\PerformanceBottleneckRecord;
use App\Models\PerformanceRollbackEvidence;
use App\Models\PlanoComercial;
use App\Models\PlatformCurrencyCatalogEntry;
use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\PlatformCurrencyRateEntry;
use App\Models\PlatformLocaleMissingKeyReport;
use App\Models\PlatformLocalePublicationRecord;
use App\Models\PoliticaInadimplencia;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\RecorteCoorteComercial;
use App\Models\RecoveryAutomationDispatch;
use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use App\Models\ReservaEstoque;
use App\Models\RetornoPagamentoSaaS;
use App\Models\RunbookExecutionEvidence;
use App\Models\SnapshotAnalyticsComercial;
use App\Models\TenantThemeVersion;
use App\Models\ThemeAssetRecord;
use App\Models\ThemePublicationRecord;
use App\Models\ThemeRollbackEvidence;
use App\Models\TransacaoFinanceira;
use App\Models\TuningChangeRecord;
use App\Models\User;
use App\Models\UsuarioPlataforma;
use App\Models\Vale;
use App\Policies\AdvancedRevenueRecoveryAutomationPolicy;
use App\Policies\AdvancedWhiteLabelPolicy;
use App\Policies\BackboneMonitoringPolicy;
use App\Policies\BoletoOrquestradoPolicy;
use App\Policies\CnabRemessaPolicy;
use App\Policies\CnabRetornoUploadPolicy;
use App\Policies\ContaBancariaPolicy;
use App\Policies\CriticalLoadOptimizationPolicy;
use App\Policies\DepositoPolicy;
use App\Policies\EstoqueMovimentacaoPolicy;
use App\Policies\ExecutiveReportingPolicy;
use App\Policies\FilaContingenciaPolicy;
use App\Policies\IntegrationBackbonePolicy;
use App\Policies\NotaFiscalOrquestradaPolicy;
use App\Policies\OrdemServicoGarantiaPolicy;
use App\Policies\OrdemServicoPolicy;
use App\Policies\PedidoVendaPolicy;
use App\Policies\PlatformBillingPolicy;
use App\Policies\PlatformCommercialAnalyticsPolicy;
use App\Policies\PlatformCurrencyPolicy;
use App\Policies\PlatformLocalizationPolicy;
use App\Policies\PlatformPaymentsPolicy;
use App\Policies\PlatformRevenueRecoveryPolicy;
use App\Policies\ProductionObservabilityPolicy;
use App\Policies\ReservaEstoquePolicy;
use App\Policies\TenantPolicy;
use App\Policies\TransacaoFinanceiraPolicy;
use App\Policies\UserPolicy;
use App\Policies\ValePolicy;
use App\Services\Contracts\Integration\EventContractRegistryContract;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Contracts\Integration\InboundEventConsumerContract;
use App\Services\Contracts\Integration\IntegrationReplayServiceContract;
use App\Services\Integration\EventContractRegistry;
use App\Services\Integration\EventPublisher;
use App\Services\Integration\InboundEventConsumer;
use App\Services\Integration\IntegrationReplayService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use OpenApi\Attributes as OA;
use Spatie\Prometheus\Facades\Prometheus;

#[OA\Info(title: 'BateriaExpert ERP API', version: '1.0.0', description: 'API central do ERP BateriaExpert com suporte a multi-tenancy e microserviços.')]
#[OA\Server(url: '/api', description: 'Servidor de API Principal')]
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(EventContractRegistryContract::class, EventContractRegistry::class);
        $this->app->bind(EventPublisherContract::class, EventPublisher::class);
        $this->app->bind(InboundEventConsumerContract::class, InboundEventConsumer::class);
        $this->app->bind(IntegrationReplayServiceContract::class, IntegrationReplayService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Cliente::class, TenantPolicy::class);
        Gate::policy(Vale::class, ValePolicy::class);
        Gate::policy(PedidoVenda::class, PedidoVendaPolicy::class);
        Gate::policy(OrdemServico::class, OrdemServicoPolicy::class);
        Gate::policy(OrdemServicoGarantia::class, OrdemServicoGarantiaPolicy::class);
        Gate::policy(Deposito::class, DepositoPolicy::class);
        Gate::policy(EstoqueMovimentacao::class, EstoqueMovimentacaoPolicy::class);
        Gate::policy(ReservaEstoque::class, ReservaEstoquePolicy::class);
        Gate::policy(ContaBancaria::class, ContaBancariaPolicy::class);
        Gate::policy(TransacaoFinanceira::class, TransacaoFinanceiraPolicy::class);
        Gate::policy(PlanoComercial::class, PlatformBillingPolicy::class);
        Gate::policy(AssinaturaPlataforma::class, PlatformBillingPolicy::class);
        Gate::policy(FaturaSaaS::class, PlatformBillingPolicy::class);
        Gate::policy(PoliticaInadimplencia::class, PlatformBillingPolicy::class);
        Gate::policy(EventoComercialAssinante::class, PlatformBillingPolicy::class);
        Gate::policy(GatewayCobrancaSaaS::class, PlatformPaymentsPolicy::class);
        Gate::policy(CobrancaSaaSExterna::class, PlatformPaymentsPolicy::class);
        Gate::policy(RetornoPagamentoSaaS::class, PlatformPaymentsPolicy::class);
        Gate::policy(ConciliacaoPagamentoSaaS::class, PlatformPaymentsPolicy::class);
        Gate::policy(ExcecaoConciliacaoSaaS::class, PlatformPaymentsPolicy::class);
        Gate::policy(PoliticaRecuperacaoReceita::class, PlatformRevenueRecoveryPolicy::class);
        Gate::policy(CasoRecuperacaoReceita::class, PlatformRevenueRecoveryPolicy::class);
        Gate::policy(AcaoRecuperacaoReceita::class, PlatformRevenueRecoveryPolicy::class);
        Gate::policy(CompromissoPagamento::class, PlatformRevenueRecoveryPolicy::class);
        Gate::policy(IndicadorRecuperacaoReceita::class, PlatformRevenueRecoveryPolicy::class);
        Gate::policy(RecoveryAutomationPolicyVersion::class, AdvancedRevenueRecoveryAutomationPolicy::class);
        Gate::policy(RecoveryAutomationJourney::class, AdvancedRevenueRecoveryAutomationPolicy::class);
        Gate::policy(RecoveryAutomationDispatch::class, AdvancedRevenueRecoveryAutomationPolicy::class);
        Gate::policy(RecoveryAutomationExperiment::class, AdvancedRevenueRecoveryAutomationPolicy::class);
        Gate::policy(RecoveryAutomationViolation::class, AdvancedRevenueRecoveryAutomationPolicy::class);
        Gate::policy(PlatformLocalePublicationRecord::class, PlatformLocalizationPolicy::class);
        Gate::policy(PlatformLocaleMissingKeyReport::class, PlatformLocalizationPolicy::class);
        Gate::policy(PlatformCurrencyCatalogEntry::class, PlatformCurrencyPolicy::class);
        Gate::policy(PlatformCurrencyPublicationRecord::class, PlatformCurrencyPolicy::class);
        Gate::policy(PlatformCurrencyRateEntry::class, PlatformCurrencyPolicy::class);
        Gate::policy(PlatformCurrencyIssueReport::class, PlatformCurrencyPolicy::class);
        Gate::policy(SnapshotAnalyticsComercial::class, PlatformCommercialAnalyticsPolicy::class);
        Gate::policy(RecorteCoorteComercial::class, PlatformCommercialAnalyticsPolicy::class);
        Gate::policy(MetricaPerformanceCanal::class, PlatformCommercialAnalyticsPolicy::class);
        Gate::policy(InsightRiscoComercial::class, PlatformCommercialAnalyticsPolicy::class);
        Gate::policy(DrilldownAnalyticsComercial::class, PlatformCommercialAnalyticsPolicy::class);
        Gate::policy(ExecutiveAnalyticsSnapshot::class, ExecutiveReportingPolicy::class);
        Gate::policy(ExecutiveReportDefinition::class, ExecutiveReportingPolicy::class);
        Gate::policy(ExecutiveReportExport::class, ExecutiveReportingPolicy::class);
        Gate::policy(ExecutiveReportExecutionLog::class, ExecutiveReportingPolicy::class);
        Gate::policy(OperationalSloDefinition::class, ProductionObservabilityPolicy::class);
        Gate::policy(OperationalAlertSnapshot::class, ProductionObservabilityPolicy::class);
        Gate::policy(LoadTestBaseline::class, ProductionObservabilityPolicy::class);
        Gate::policy(OperationalIncidentRecord::class, ProductionObservabilityPolicy::class);
        Gate::policy(RunbookExecutionEvidence::class, ProductionObservabilityPolicy::class);
        Gate::policy(BrandIdentityProfile::class, AdvancedWhiteLabelPolicy::class);
        Gate::policy(TenantThemeVersion::class, AdvancedWhiteLabelPolicy::class);
        Gate::policy(ThemeAssetRecord::class, AdvancedWhiteLabelPolicy::class);
        Gate::policy(ThemePublicationRecord::class, AdvancedWhiteLabelPolicy::class);
        Gate::policy(ThemeRollbackEvidence::class, AdvancedWhiteLabelPolicy::class);
        Gate::policy(LoadScenarioProfile::class, CriticalLoadOptimizationPolicy::class);
        Gate::policy(BenchmarkExecutionRecord::class, CriticalLoadOptimizationPolicy::class);
        Gate::policy(PerformanceBottleneckRecord::class, CriticalLoadOptimizationPolicy::class);
        Gate::policy(TuningChangeRecord::class, CriticalLoadOptimizationPolicy::class);
        Gate::policy(PerformanceRollbackEvidence::class, CriticalLoadOptimizationPolicy::class);
        Gate::policy(MonitoringTargetCatalog::class, BackboneMonitoringPolicy::class);
        Gate::policy(MonitoringProbeSnapshot::class, BackboneMonitoringPolicy::class);
        Gate::policy(AlertRuleDefinition::class, BackboneMonitoringPolicy::class);
        Gate::policy(DashboardProvisioningRecord::class, BackboneMonitoringPolicy::class);
        Gate::policy(MonitoringReadinessEvidence::class, BackboneMonitoringPolicy::class);
        Gate::policy(NotaFiscalOrquestrada::class, NotaFiscalOrquestradaPolicy::class);
        Gate::policy(BoletoOrquestrado::class, BoletoOrquestradoPolicy::class);
        Gate::policy(CnabRemessa::class, CnabRemessaPolicy::class);
        Gate::policy(CnabRetornoUpload::class, CnabRetornoUploadPolicy::class);
        Gate::policy(FilaContingencia::class, FilaContingenciaPolicy::class);
        Gate::policy(EntregaIntegracao::class, IntegrationBackbonePolicy::class);

        // Configurações Globais de SEO
        seo()
            ->site('Promovaweb')
            ->title(
                default: 'Laravel 12 Jetstream Livewire Starter Kit',
                modify: fn (string $title) => $title.' | Promovaweb'
            )
            ->description(default: 'We are a development agency ...')
            ->twitterSite('@promovaweb');

        // RBAC Gates
        Gate::define('gerenciar-usuarios', function (User $user) {
            return $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('view-platform-dashboard', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('view-platform-localization', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('use-platform-localization', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-platform-localization', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support']);
        });

        Gate::define('rollback-platform-localization', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->isSuperAdmin();
        });

        Gate::define('view-platform-currencies', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('use-platform-currencies', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-platform-currencies', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('rollback-platform-currencies', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->isSuperAdmin();
        });

        Gate::define('manage-tenants', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->isSuperAdmin();
        });

        Gate::define('manage-platform-billing', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-platform-payments', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-platform-revenue-recovery', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-advanced-revenue-recovery-automation', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-platform-commercial-analytics', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-executive-reporting', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'billing']);
        });

        Gate::define('manage-production-observability', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-backbone-monitoring', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-critical-load-optimization', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-advanced-white-label', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support', 'billing']);
        });

        Gate::define('manage-platform-support', function ($user) {
            return $user instanceof UsuarioPlataforma
                && $user->ativo
                && $user->hasRole(['super_admin', 'support']);
        });

        Gate::define('gerenciar-assinatura', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole('dono');
        });

        Gate::define('acesso-vendas', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('acesso-estoque', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'estoquista']);
        });

        Gate::define('acesso-logistica', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'entregador']);
        });

        Gate::define('acesso-financeiro', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('acesso-tecnico', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'tecnico']);
        });

        Gate::define('gerenciar-vales', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('gerenciar-pedidos-venda', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('gerenciar-ordens-servico', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'tecnico']);
        });

        Gate::define('movimentar-estoque', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'estoquista']);
        });

        Gate::define('gerenciar-financeiro-avancado', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('emitir-documentos-fiscais', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor', 'vendedor']);
        });

        Gate::define('processar-cnab', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('gerenciar-contingencia', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('view-integration-operations', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        Gate::define('replay-integration-events', function (User $user) {
            return $user->isSuperAdmin() || $user->hasRole(['dono', 'gestor']);
        });

        // Registro de métricas do Circuit Breaker
        Prometheus::addCounter('circuit_breaker_events_total')
            ->label('service')
            ->label('event');

        Prometheus::addCounter('circuit_breaker_rejected_calls_total')
            ->label('service');

        Prometheus::addCounter('circuit_breaker_fallback_executions_total')
            ->label('service')
            ->label('reason');

        Prometheus::addCounter('integration_events_total')
            ->label('direction')
            ->label('event_type')
            ->label('status');

        Prometheus::addCounter('integration_replays_total')
            ->label('target')
            ->label('status');

        Prometheus::addGauge('integration_outbox_total')
            ->label('status');

        Prometheus::addGauge('integration_deliveries_total')
            ->label('direction')
            ->label('status');

        Prometheus::addGauge('integration_delivery_latency_average_ms')
            ->label('direction')
            ->label('target');

        Prometheus::addGauge('integration_contracts_catalog_total')
            ->label('status');

        Prometheus::addGauge('integration_gateway_endpoints_total')
            ->label('service_name')
            ->label('status');
    }
}
