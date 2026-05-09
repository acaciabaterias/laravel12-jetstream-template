<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\PlanoComercial;
use App\Services\Billing\CommercialAnalyticsSnapshotService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformCommercialAnalyticsCohortTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
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

    public function test_it_builds_cohort_cuts_from_subscription_start_month(): void
    {
        $plan = PlanoComercial::factory()->create(['preco_mensal' => 120]);

        foreach ([['2026-03-01', 'active'], ['2026-03-15', 'cancelled'], ['2026-04-10', 'past_due']] as [$date, $status]) {
            $cliente = Cliente::factory()->create();
            AssinaturaPlataforma::factory()->create([
                'cliente_id' => $cliente->id,
                'plano_id' => $plan->id,
                'data_inicio' => $date,
                'status' => $status,
                'data_termino' => $status === 'cancelled' ? '2026-05-01' : null,
            ]);
        }

        $snapshot = app(CommercialAnalyticsSnapshotService::class)->rebuild(periodEnd: Carbon::parse('2026-05-08'), days: 30);

        $this->assertDatabaseHas('recortes_coorte_comercial', [
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'cohort_label' => '2026-03',
            'cancelled_subscriptions' => 1,
        ], 'central');

        $this->assertDatabaseHas('recortes_coorte_comercial', [
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'cohort_label' => '2026-04',
            'delinquent_subscriptions' => 1,
        ], 'central');
    }
}
