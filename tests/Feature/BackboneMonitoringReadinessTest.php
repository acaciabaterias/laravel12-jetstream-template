<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MonitoringTargetCatalog;
use App\Services\Operations\MonitoringReadinessService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BackboneMonitoringReadinessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('monitoring_consolidation.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_13_160000_create_central_monitoring_target_catalogs_table.php',
            'database/migrations/central/2026_05_13_160100_create_central_monitoring_probe_snapshots_table.php',
            'database/migrations/central/2026_05_13_160200_create_central_alert_rule_definitions_table.php',
            'database/migrations/central/2026_05_13_160300_create_central_dashboard_provisioning_records_table.php',
            'database/migrations/central/2026_05_13_160400_create_central_monitoring_readiness_evidences_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_it_records_probe_snapshots_and_marks_unavailable_targets_explicitly(): void
    {
        Http::fake([
            'https://metrics.example.test/healthy' => Http::response(['sample_count' => 12], 200),
            'https://metrics.example.test/down' => Http::response([], 503),
        ]);

        $healthyTarget = MonitoringTargetCatalog::factory()->create([
            'target_name' => 'healthy-target',
            'endpoint' => 'https://metrics.example.test/healthy',
        ]);
        $downTarget = MonitoringTargetCatalog::factory()->create([
            'target_name' => 'down-target',
            'endpoint' => 'https://metrics.example.test/down',
        ]);

        $service = app(MonitoringReadinessService::class);
        $healthySnapshot = $service->refreshTarget($healthyTarget);
        $downSnapshot = $service->refreshTarget($downTarget);

        $this->assertSame('healthy', $healthySnapshot->scrape_status->value);
        $this->assertSame(12, $healthySnapshot->sample_count);
        $this->assertSame('unavailable', $downSnapshot->scrape_status->value);
        $this->assertSame('http_status_503', $downSnapshot->failure_reason);
        $this->assertSame('unavailable', $downTarget->fresh()->status->value);

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'SCRAPE_HEALTH_CRITICO',
            'origin_context' => 'monitoring-consolidation',
        ], 'central');
    }
}
