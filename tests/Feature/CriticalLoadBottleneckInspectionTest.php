<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadScenarioProfile;
use App\Services\Operations\CriticalLoadBenchmarkService;
use App\Services\Operations\CriticalLoadBottleneckAnalysisService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CriticalLoadBottleneckInspectionTest extends TestCase
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

    public function test_it_records_critical_bottlenecks_and_publishes_material_event(): void
    {
        $scenario = LoadScenarioProfile::factory()->create([
            'flow_name' => 'integration_backbone',
        ]);
        $execution = app(CriticalLoadBenchmarkService::class)->recordExecution($scenario, [
            'throughput_per_minute' => 300,
            'p95_latency_ms' => 900,
            'error_rate' => 0.01,
        ]);

        $bottleneck = app(CriticalLoadBottleneckAnalysisService::class)->record($execution, [
            'flow_name' => 'integration_backbone',
            'category' => 'database',
            'component_name' => 'evento_outboxes_lookup',
            'summary' => 'Consulta sem indice degrada durante picos.',
            'impact_level' => 'critical',
        ]);

        $this->assertSame('database', $bottleneck->category->value);
        $this->assertSame('critical', $bottleneck->impact_level->value);
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'GARGALO_CRITICO_IDENTIFICADO',
            'origin_context' => 'critical-load-optimization',
        ], 'central');
    }
}
