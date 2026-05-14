<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadScenarioProfile;
use App\Models\UsuarioPlataforma;
use App\Services\Operations\CriticalLoadBenchmarkService;
use App\Services\Operations\CriticalLoadTuningLifecycleService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CriticalLoadTuningInspectionTest extends TestCase
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

    public function test_inspection_returns_tuning_changes_by_environment_and_status(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $scenario = LoadScenarioProfile::factory()->create([
            'flow_name' => 'platform_payments',
            'environment' => 'production',
            'expected_throughput_per_minute' => 500,
            'expected_p95_latency_ms' => 1000,
            'expected_error_rate' => 0.01,
        ]);
        $baseline = app(CriticalLoadBenchmarkService::class)->recordExecution($scenario, [
            'throughput_per_minute' => 520,
            'p95_latency_ms' => 900,
            'error_rate' => 0.01,
        ]);
        $validation = app(CriticalLoadBenchmarkService::class)->recordExecution($scenario, [
            'throughput_per_minute' => 540,
            'p95_latency_ms' => 850,
            'error_rate' => 0.009,
        ]);
        $change = app(CriticalLoadTuningLifecycleService::class)->register([
            'flow_name' => 'platform_payments',
            'environment' => 'production',
            'change_key' => 'payments-index-v2',
            'hypothesis_summary' => 'Indice composto reduz lookup de conciliacao.',
            'change_type' => 'index',
            'baseline_execution_id' => $baseline->id,
        ]);
        app(CriticalLoadTuningLifecycleService::class)->validate($change, $validation);

        $response = $this->actingAs($support, 'platform')
            ->getJson(route('admin.capacity.inspection', [
                'environment' => 'production',
                'tuning_status' => 'validated',
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'tuning_changes')
            ->assertJsonPath('tuning_changes.0.change_key', 'payments-index-v2')
            ->assertJsonPath('tuning_changes.0.status', 'validated')
            ->assertJsonPath('tuning_changes.0.environment', 'production');
    }
}
