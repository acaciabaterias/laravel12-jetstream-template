<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PlanoComercial;
use App\Models\PoliticaRecuperacaoReceita;
use App\Services\Billing\CommercialAnalyticsSnapshotService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformCommercialAnalyticsSegmentationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
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

    public function test_it_groups_snapshot_by_cohort_and_channel_with_consistent_rates(): void
    {
        $policy = PoliticaRecuperacaoReceita::factory()->create();
        $plan = PlanoComercial::factory()->create(['preco_mensal' => 100]);
        $clienteA = Cliente::factory()->create();
        $clienteB = Cliente::factory()->create();

        $subscriptionA = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $clienteA->id,
            'plano_id' => $plan->id,
            'status' => 'active',
            'data_inicio' => '2026-04-01',
        ]);
        $subscriptionB = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $clienteB->id,
            'plano_id' => $plan->id,
            'status' => 'cancelled',
            'data_inicio' => '2026-04-10',
            'data_termino' => '2026-05-02',
        ]);

        FaturaSaaS::factory()->create([
            'cliente_id' => $clienteA->id,
            'assinatura_id' => $subscriptionA->id,
            'status' => 'paid',
            'billing_channel' => 'pix',
            'valor_pago' => 100,
            'vencimento' => '2026-05-01',
        ]);
        $invoiceB = FaturaSaaS::factory()->create([
            'cliente_id' => $clienteB->id,
            'assinatura_id' => $subscriptionB->id,
            'status' => 'overdue',
            'billing_channel' => 'pix',
            'valor' => 100,
            'vencimento' => '2026-05-02',
        ]);

        $case = CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $clienteB->id,
            'assinatura_id' => $subscriptionB->id,
            'fatura_saas_id' => $invoiceB->id,
            'politica_recuperacao_receita_id' => $policy->id,
        ]);
        AcaoRecuperacaoReceita::factory()->create([
            'caso_recuperacao_receita_id' => $case->id,
            'channel' => 'email',
            'status' => 'failed',
            'created_at' => '2026-05-03 09:00:00',
        ]);

        $snapshot = app(CommercialAnalyticsSnapshotService::class)->rebuild(periodEnd: Carbon::parse('2026-05-08'), days: 30);

        $cohort = $snapshot->cohorts()->where('cohort_label', '2026-04')->firstOrFail();
        $billingChannel = $snapshot->channelMetrics()->where('channel_type', 'billing')->where('channel_name', 'pix')->firstOrFail();
        $recoveryChannel = $snapshot->channelMetrics()->where('channel_type', 'recovery')->where('channel_name', 'email')->firstOrFail();

        $this->assertSame(1, $cohort->active_subscriptions);
        $this->assertSame(1, $cohort->cancelled_subscriptions);
        $this->assertSame(0.5, round((float) $billingChannel->conversion_rate, 4));
        $this->assertSame(0.0, round((float) $recoveryChannel->conversion_rate, 4));
    }
}
