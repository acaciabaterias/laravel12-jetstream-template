<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\DrilldownAnalyticsComercial;
use App\Models\SnapshotAnalyticsComercial;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PlatformCommercialAnalyticsDrilldownTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_08_220105_create_central_snapshot_analytics_comercials_table.php',
            'database/migrations/central/2026_05_08_220109_create_central_drilldown_analytics_comercials_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_billing_operator_can_filter_analytics_drilldown_inspection(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        $snapshot = SnapshotAnalyticsComercial::factory()->create();
        DrilldownAnalyticsComercial::factory()->create([
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'metric_key' => 'delinquency',
            'source_type' => 'invoice',
        ]);
        DrilldownAnalyticsComercial::factory()->create([
            'snapshot_analytics_comercial_id' => $snapshot->id,
            'metric_key' => 'mrr',
            'source_type' => 'subscription',
        ]);

        $response = $this
            ->actingAs($operador, 'platform')
            ->getJson(route('admin.analytics.inspection', ['snapshot_id' => $snapshot->id, 'metric_key' => 'delinquency']));

        $response
            ->assertOk()
            ->assertJsonCount(1, 'drilldowns')
            ->assertJsonPath('drilldowns.0.metric_key', 'delinquency')
            ->assertJsonPath('drilldowns.0.source_type', 'invoice');
    }
}
