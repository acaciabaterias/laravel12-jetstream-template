<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadTestBaseline;
use App\Models\OperationalAlertSnapshot;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionObservabilityFlowFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_09_133558_create_central_operational_alert_snapshots_table.php',
            'database/migrations/central/2026_05_09_133559_create_central_load_test_baselines_table.php',
            'database/migrations/central/2026_05_09_133600_create_central_operational_incident_records_table.php',
            'database/migrations/central/2026_05_09_133601_create_central_runbook_execution_evidences_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_operational_inspection_filters_by_flow_and_includes_baseline_comparison(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        OperationalAlertSnapshot::factory()->create([
            'flow_name' => 'platform_payments',
            'severity' => 'warning',
            'status' => 'degraded',
        ]);
        OperationalAlertSnapshot::factory()->create([
            'flow_name' => 'platform_recovery',
            'severity' => 'critical',
            'status' => 'degraded',
        ]);

        LoadTestBaseline::factory()->create([
            'scenario_name' => 'peak-payments',
            'flow_name' => 'platform_payments',
            'throughput_per_minute' => 500,
            'p95_latency_ms' => 1000,
            'error_rate' => 0.01,
        ]);
        LoadTestBaseline::factory()->create([
            'scenario_name' => 'nightly-analytics',
            'flow_name' => 'platform_analytics',
            'throughput_per_minute' => 120,
            'p95_latency_ms' => 900,
            'error_rate' => 0.02,
        ]);

        $response = $this->actingAs($support, 'platform')
            ->getJson(route('admin.operations.inspection', [
                'flow_name' => 'platform_payments',
                'scenario_name' => 'peak-payments',
                'throughput_per_minute' => 480,
                'p95_latency_ms' => 1100,
                'error_rate' => 0.015,
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'snapshots')
            ->assertJsonPath('snapshots.0.flow_name', 'platform_payments')
            ->assertJsonCount(1, 'baselines')
            ->assertJsonPath('baselines.0.flow_name', 'platform_payments')
            ->assertJsonPath('comparison.status', 'within_tolerance')
            ->assertJsonPath('comparison.baseline.scenario_name', 'peak-payments');
    }
}
