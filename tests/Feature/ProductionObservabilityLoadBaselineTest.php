<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Services\Operations\LoadTestBaselineService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionObservabilityLoadBaselineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('production_observability.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_09_133559_create_central_load_test_baselines_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_persists_load_baselines_and_flags_regressions_beyond_tolerance(): void
    {
        $service = app(LoadTestBaselineService::class);

        $baseline = $service->record([
            'scenario_name' => 'peak-payments',
            'flow_name' => 'platform_payments',
            'throughput_per_minute' => 500,
            'p95_latency_ms' => 1000,
            'error_rate' => 0.01,
            'environment_notes' => 'staging with production-like queues',
            'accepted_at' => now(),
            'metadata' => ['operator' => 'test-suite'],
        ]);

        $comparison = $service->compare([
            'scenario_name' => 'peak-payments',
            'flow_name' => 'platform_payments',
            'throughput_per_minute' => 400,
            'p95_latency_ms' => 1300,
            'error_rate' => 0.05,
        ]);

        $this->assertNotNull($baseline->id);
        $this->assertSame('regressed', $comparison['status']);
        $this->assertFalse($comparison['within_tolerance']);
        $this->assertSame([
            'throughput_per_minute',
            'p95_latency_ms',
            'error_rate',
        ], $comparison['regressed_metrics']);

        $this->assertDatabaseHas('load_test_baselines', [
            'id' => $baseline->id,
            'scenario_name' => 'peak-payments',
            'flow_name' => 'platform_payments',
            'throughput_per_minute' => 500,
        ], 'central');

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'BASELINE_CARGA_ATUALIZADO',
            'origin_context' => 'production-observability',
            'tenant_external_ref' => 'platform-central',
        ], 'central');
    }
}
