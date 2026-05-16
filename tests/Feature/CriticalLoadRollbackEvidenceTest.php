<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadScenarioProfile;
use App\Models\UsuarioPlataforma;
use App\Services\Operations\CriticalLoadBenchmarkService;
use App\Services\Operations\CriticalLoadRollbackEvidenceService;
use App\Services\Operations\CriticalLoadTuningLifecycleService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CriticalLoadRollbackEvidenceTest extends TestCase
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

    public function test_it_records_rollback_evidence_and_publishes_material_event(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create(['name' => 'Operador Tuning']);
        $scenario = LoadScenarioProfile::factory()->create([
            'flow_name' => 'integration_backbone',
            'environment' => 'staging',
            'expected_throughput_per_minute' => 500,
            'expected_p95_latency_ms' => 1000,
            'expected_error_rate' => 0.01,
        ]);
        $baseline = app(CriticalLoadBenchmarkService::class)->recordExecution($scenario, [
            'throughput_per_minute' => 520,
            'p95_latency_ms' => 900,
            'error_rate' => 0.01,
        ]);
        $regressed = app(CriticalLoadBenchmarkService::class)->recordExecution($scenario, [
            'throughput_per_minute' => 430,
            'p95_latency_ms' => 1300,
            'error_rate' => 0.03,
        ]);
        $change = app(CriticalLoadTuningLifecycleService::class)->register([
            'flow_name' => 'integration_backbone',
            'environment' => 'staging',
            'change_key' => 'outbox-query-rewrite',
            'hypothesis_summary' => 'Rewrite da consulta do outbox.',
            'change_type' => 'query_rewrite',
            'baseline_execution_id' => $baseline->id,
        ]);
        $change = app(CriticalLoadTuningLifecycleService::class)->validate($change, $regressed);
        $change = app(CriticalLoadTuningLifecycleService::class)->rollback($change);
        $evidence = app(CriticalLoadRollbackEvidenceService::class)->record($change, [
            'operator_user_id' => $operator->id,
            'result_status' => 'success',
            'rollback_reason' => 'Regressao acima da tolerancia validada.',
        ]);

        $this->assertSame('success', $evidence->result_status->value);
        $this->assertSame('outbox-query-rewrite', $evidence->payload['change_key']);
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ROLLBACK_PERFORMANCE_EXECUTADO',
            'origin_context' => 'critical-load-optimization',
        ], 'central');
    }
}
