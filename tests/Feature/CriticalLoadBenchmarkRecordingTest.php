<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadScenarioProfile;
use App\Services\Operations\CriticalLoadBenchmarkService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CriticalLoadBenchmarkRecordingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('load_optimization.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_13_170000_create_central_load_scenario_profiles_table.php',
            'database/migrations/central/2026_05_13_170100_create_central_benchmark_execution_records_table.php',
            'database/migrations/central/2026_05_13_170200_create_central_performance_bottleneck_records_table.php',
            'database/migrations/central/2026_05_13_170300_create_central_tuning_change_records_table.php',
            'database/migrations/central/2026_05_13_170400_create_central_performance_rollback_evidences_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_records_benchmark_executions_and_publishes_regression_events(): void
    {
        $scenario = LoadScenarioProfile::factory()->create([
            'flow_name' => 'platform_payments',
            'scenario_name' => 'peak-payments',
            'expected_throughput_per_minute' => 500,
            'expected_p95_latency_ms' => 1000,
            'expected_error_rate' => 0.01,
        ]);

        $service = app(CriticalLoadBenchmarkService::class);
        $regressed = $service->recordExecution($scenario, [
            'throughput_per_minute' => 420,
            'p95_latency_ms' => 1300,
            'error_rate' => 0.03,
        ]);
        $stable = $service->recordExecution($scenario, [
            'throughput_per_minute' => 490,
            'p95_latency_ms' => 1080,
            'error_rate' => 0.015,
        ]);

        $this->assertSame('regressed', $regressed->comparison_status->value);
        $this->assertSame('stable', $stable->comparison_status->value);

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'BENCHMARK_REGRESSIVO_DETECTADO',
            'origin_context' => 'critical-load-optimization',
        ], 'central');
    }
}
