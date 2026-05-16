<?php

declare(strict_types=1);

namespace Tests\Concerns;

use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\GatewayCobrancaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaInadimplencia;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\RetornoPagamentoSaaS;
use App\Services\Billing\SaasInvoiceService;
use App\Services\Billing\SubscriptionLifecycleService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

trait InteractsWithExecutiveReportingFixtures
{
    protected function runExecutiveReportingMigrations(): void
    {
        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
            'database/migrations/central/2026_05_08_220105_create_central_snapshot_analytics_comercials_table.php',
            'database/migrations/central/2026_05_08_220106_create_central_recorte_coorte_comercials_table.php',
            'database/migrations/central/2026_05_08_220107_create_central_metrica_performance_canals_table.php',
            'database/migrations/central/2026_05_08_220108_create_central_insight_risco_comercials_table.php',
            'database/migrations/central/2026_05_08_220109_create_central_drilldown_analytics_comercials_table.php',
            'database/migrations/central/2026_05_13_190000_create_central_executive_analytics_snapshots_table.php',
            'database/migrations/central/2026_05_13_190100_create_central_executive_report_definitions_table.php',
            'database/migrations/central/2026_05_13_190200_create_central_executive_report_exports_table.php',
            'database/migrations/central/2026_05_13_190300_create_central_executive_report_execution_logs_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    protected function seedExecutiveReportingScenario(): void
    {
        $essentialPlan = PlanoComercial::factory()->create([
            'nome' => 'Plano Essential',
            'slug' => 'essential',
            'preco_mensal' => 299.90,
        ]);
        $enterprisePlan = PlanoComercial::factory()->create([
            'nome' => 'Plano Enterprise',
            'slug' => 'enterprise',
            'preco_mensal' => 899.90,
        ]);
        $policy = PoliticaInadimplencia::factory()->create();
        $recoveryPolicy = PoliticaRecuperacaoReceita::factory()->create();
        $gateway = GatewayCobrancaSaaS::factory()->create();

        $essentialClient = Cliente::factory()->create([
            'razao_social' => 'Cliente Essential',
            'subdominio' => 'cliente-essential',
        ]);
        $enterpriseClient = Cliente::factory()->create([
            'razao_social' => 'Cliente Enterprise',
            'subdominio' => 'cliente-enterprise',
        ]);

        $activeSubscription = app(SubscriptionLifecycleService::class)->activate(
            cliente: $essentialClient,
            planoComercial: $essentialPlan,
            politicaInadimplencia: $policy,
        );
        $blockedSubscription = app(SubscriptionLifecycleService::class)->activate(
            cliente: $enterpriseClient,
            planoComercial: $enterprisePlan,
            politicaInadimplencia: $policy,
        );
        $blockedSubscription->forceFill([
            'status' => 'blocked',
            'blocked_at' => now(),
        ])->save();

        $paidInvoice = app(SaasInvoiceService::class)->createInvoice($activeSubscription, [
            'vencimento' => Carbon::now()->subDays(5)->toDateString(),
            'valor' => 299.90,
            'billing_channel' => 'pix',
        ]);
        $paidInvoice->forceFill([
            'status' => 'paid',
            'valor_pago' => 299.90,
            'paid_at' => now()->subDays(4),
        ])->save();

        $overdueInvoice = app(SaasInvoiceService::class)->createInvoice($blockedSubscription, [
            'vencimento' => Carbon::now()->subDays(3)->toDateString(),
            'valor' => 899.90,
            'billing_channel' => 'boleto',
        ]);
        $overdueInvoice->forceFill([
            'status' => 'overdue',
        ])->save();

        CasoRecuperacaoReceita::query()->create([
            'cliente_id' => $enterpriseClient->id,
            'assinatura_id' => $blockedSubscription->id,
            'fatura_saas_id' => $overdueInvoice->id,
            'politica_recuperacao_receita_id' => $recoveryPolicy->id,
            'status' => 'open',
            'entry_reason' => 'invoice_overdue',
            'current_stage' => 'negotiation',
            'severity' => 'high',
            'opened_at' => now()->subDays(2),
            'last_action_at' => now()->subDay(),
            'metadata' => ['source' => 'test'],
        ]);

        RetornoPagamentoSaaS::query()->create([
            'gateway_cobranca_saas_id' => $gateway->id,
            'cobranca_saas_externa_id' => null,
            'source_type' => 'webhook',
            'external_event_id' => 'evt_'.uniqid(),
            'external_reference' => 'ref_'.uniqid(),
            'event_type' => 'payment_failed',
            'payload' => ['invoice_id' => $overdueInvoice->id],
            'received_at' => now()->subDay(),
            'processed_at' => now()->subDay(),
            'processing_status' => 'failed',
            'processing_error' => 'gateway_timeout',
            'idempotency_key' => 'payment-failed-'.uniqid(),
            'metadata' => ['source' => 'test'],
        ]);
    }
}
