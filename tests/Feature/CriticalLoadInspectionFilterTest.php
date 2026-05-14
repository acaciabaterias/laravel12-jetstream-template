<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadScenarioProfile;
use App\Models\UsuarioPlataforma;
use App\Services\Operations\CriticalLoadBenchmarkService;
use App\Services\Operations\CriticalLoadBottleneckAnalysisService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CriticalLoadInspectionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
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

    public function test_critical_load_inspection_filters_by_flow_and_comparison_status(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $benchmarkService = app(CriticalLoadBenchmarkService::class);
        $bottleneckService = app(CriticalLoadBottleneckAnalysisService::class);

        $paymentsScenario = LoadScenarioProfile::factory()->create([
            'flow_name' => 'platform_payments',
            'scenario_name' => 'peak-payments',
            'expected_throughput_per_minute' => 500,
            'expected_p95_latency_ms' => 1000,
            'expected_error_rate' => 0.01,
        ]);
        $recoveryScenario = LoadScenarioProfile::factory()->create([
            'flow_name' => 'platform_recovery',
            'scenario_name' => 'recovery-nightly',
            'expected_throughput_per_minute' => 480,
            'expected_p95_latency_ms' => 950,
            'expected_error_rate' => 0.01,
        ]);

        $paymentsExecution = $benchmarkService->recordExecution($paymentsScenario, [
            'throughput_per_minute' => 420,
            'p95_latency_ms' => 1300,
            'error_rate' => 0.03,
        ]);
        $benchmarkService->recordExecution($recoveryScenario, [
            'throughput_per_minute' => 500,
            'p95_latency_ms' => 900,
            'error_rate' => 0.01,
        ]);

        $bottleneckService->record($paymentsExecution, [
            'flow_name' => 'platform_payments',
            'category' => 'queue',
            'component_name' => 'payment-return-worker',
            'summary' => 'Fila de retorno acumulou backlog.',
            'impact_level' => 'warning',
        ]);

        $response = $this->actingAs($support, 'platform')
            ->getJson(route('admin.capacity.inspection', [
                'flow_name' => 'platform_payments',
                'comparison_status' => 'regressed',
                'category' => 'queue',
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'scenarios')
            ->assertJsonPath('scenarios.0.flow_name', 'platform_payments')
            ->assertJsonCount(1, 'executions')
            ->assertJsonPath('executions.0.comparison_status', 'regressed')
            ->assertJsonCount(1, 'bottlenecks')
            ->assertJsonPath('bottlenecks.0.category', 'queue');
    }
}
