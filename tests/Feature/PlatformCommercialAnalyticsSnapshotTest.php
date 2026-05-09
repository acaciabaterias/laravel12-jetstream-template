<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Services\Billing\CommercialAnalyticsSnapshotService;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PlatformCommercialAnalyticsSnapshotTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_commercial_analytics.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
            'database/migrations/central/2026_05_08_220105_create_central_snapshot_analytics_comercials_table.php',
            'database/migrations/central/2026_05_08_220106_create_central_recorte_coorte_comercials_table.php',
            'database/migrations/central/2026_05_08_220107_create_central_metrica_performance_canals_table.php',
            'database/migrations/central/2026_05_08_220108_create_central_insight_risco_comercials_table.php',
            'database/migrations/central/2026_05_08_220109_create_central_drilldown_analytics_comercials_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_generates_a_snapshot_with_executive_metrics_and_related_cuts(): void
    {
        Queue::fake();

        $periodEnd = Carbon::parse('2026-05-08');
        $policy = PoliticaRecuperacaoReceita::factory()->create();

        $activeCustomer = Cliente::factory()->create(['subdominio' => 'tenant-active']);
        $blockedCustomer = Cliente::factory()->create(['subdominio' => 'tenant-blocked', 'billing_blocked' => true]);
        $cancelledCustomer = Cliente::factory()->create(['subdominio' => 'tenant-cancelled']);

        $basicPlan = PlanoComercial::factory()->create(['preco_mensal' => 100]);
        $proPlan = PlanoComercial::factory()->create(['preco_mensal' => 200]);
        $legacyPlan = PlanoComercial::factory()->create(['preco_mensal' => 150]);

        $activeSubscription = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $activeCustomer->id,
            'plano_id' => $basicPlan->id,
            'status' => 'active',
            'data_inicio' => '2026-04-01',
        ]);
        $pastDueSubscription = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $blockedCustomer->id,
            'plano_id' => $proPlan->id,
            'status' => 'past_due',
            'data_inicio' => '2026-04-10',
        ]);
        $cancelledSubscription = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cancelledCustomer->id,
            'plano_id' => $legacyPlan->id,
            'status' => 'cancelled',
            'data_inicio' => '2026-03-01',
            'data_termino' => '2026-05-02',
        ]);

        $paidInvoice = FaturaSaaS::factory()->create([
            'cliente_id' => $activeCustomer->id,
            'assinatura_id' => $activeSubscription->id,
            'status' => 'paid',
            'billing_channel' => 'pix',
            'valor' => 100,
            'valor_pago' => 100,
            'vencimento' => '2026-05-05',
            'paid_at' => '2026-05-05 10:00:00',
        ]);
        $overdueInvoice = FaturaSaaS::factory()->create([
            'cliente_id' => $blockedCustomer->id,
            'assinatura_id' => $pastDueSubscription->id,
            'status' => 'overdue',
            'billing_channel' => 'boleto',
            'valor' => 200,
            'valor_pago' => null,
            'vencimento' => '2026-05-01',
        ]);

        $recoveredCase = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $activeCustomer->id,
            'assinatura_id' => $activeSubscription->id,
            'fatura_saas_id' => $paidInvoice->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'status' => RevenueRecoveryCaseStatus::Recovered->value,
            'updated_at' => '2026-05-07 12:00:00',
            'last_action_at' => '2026-05-07 12:00:00',
        ]);

        CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $blockedCustomer->id,
            'assinatura_id' => $pastDueSubscription->id,
            'fatura_saas_id' => $overdueInvoice->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'current_stage' => 'd3',
            'updated_at' => '2026-05-06 10:00:00',
            'last_action_at' => '2026-05-01 10:00:00',
        ]);

        AcaoRecuperacaoReceita::factory()->create([
            'caso_recuperacao_receita_id' => $recoveredCase->id,
            'channel' => 'whatsapp',
            'status' => 'completed',
            'created_at' => '2026-05-07 09:00:00',
        ]);

        $snapshot = app(CommercialAnalyticsSnapshotService::class)->rebuild(periodEnd: $periodEnd, days: 30);

        $this->assertSame(300.0, (float) $snapshot->mrr_amount);
        $this->assertSame(1, $snapshot->churn_count);
        $this->assertSame(1, $snapshot->delinquent_count);
        $this->assertSame(1, $snapshot->recovered_count);
        $this->assertSame(100.0, (float) $snapshot->recovered_amount);
        $this->assertSame(1, $snapshot->blocked_count);
        $this->assertSame(30, $snapshot->metadata['days']);
        $this->assertNotEmpty($snapshot->metadata['active_subscription_ids']);
        $this->assertGreaterThan(0, $snapshot->cohorts()->count());
        $this->assertGreaterThan(0, $snapshot->channelMetrics()->count());
        $this->assertGreaterThan(0, $snapshot->riskInsights()->count());
        $this->assertGreaterThan(0, $snapshot->drilldowns()->count());

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'SNAPSHOT_ANALYTICS_ATUALIZADO',
            'tenant_external_ref' => 'platform-central',
            'origin_context' => 'platform-commercial-analytics',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'COORTE_COMERCIAL_ATUALIZADA',
            'origin_context' => 'platform-commercial-analytics',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'INSIGHT_RISCO_IDENTIFICADO',
            'origin_context' => 'platform-commercial-analytics',
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'CANAL_PERFORMANCE_DEGRADADO',
            'origin_context' => 'platform-commercial-analytics',
        ], 'central');
    }
}
