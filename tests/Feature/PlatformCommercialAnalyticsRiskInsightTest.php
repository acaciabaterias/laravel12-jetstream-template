<?php

declare(strict_types=1);

namespace Tests\Feature;

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
use Tests\TestCase;

class PlatformCommercialAnalyticsRiskInsightTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_generates_delinquency_and_recovery_stall_risks(): void
    {
        $policy = PoliticaRecuperacaoReceita::factory()->create();
        $cliente = Cliente::factory()->create();
        $plan = PlanoComercial::factory()->create();
        $subscription = AssinaturaPlataforma::factory()->create([
            'cliente_id' => $cliente->id,
            'plano_id' => $plan->id,
            'status' => 'past_due',
        ]);
        $invoice = FaturaSaaS::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $subscription->id,
            'status' => 'overdue',
            'valor' => 250,
            'vencimento' => '2026-05-01',
        ]);
        CasoRecuperacaoReceita::factory()->create([
            'cliente_id' => $cliente->id,
            'assinatura_id' => $subscription->id,
            'fatura_saas_id' => $invoice->id,
            'politica_recuperacao_receita_id' => $policy->id,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'last_action_at' => '2026-04-28 10:00:00',
        ]);

        $snapshot = app(CommercialAnalyticsSnapshotService::class)->rebuild(periodEnd: Carbon::parse('2026-05-08'), days: 30);

        $this->assertDatabaseHas('insights_risco_comercial', [
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'risk_type' => 'delinquency',
        ], 'central');

        $this->assertDatabaseHas('insights_risco_comercial', [
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'risk_type' => 'recovery_stall',
        ], 'central');
    }
}
