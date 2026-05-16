<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AlertRuleDefinition;
use App\Models\MonitoringProbeSnapshot;
use App\Models\MonitoringTargetCatalog;
use App\Services\Operations\BackboneMonitoringInspectionService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackboneMonitoringAlertRulesTest extends TestCase
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

    public function test_it_persists_and_evaluates_applied_alert_rules(): void
    {
        $target = MonitoringTargetCatalog::factory()->create([
            'flow_name' => 'platform_payments',
            'target_name' => 'prometheus-payments',
            'status' => 'degraded',
        ]);
        MonitoringProbeSnapshot::factory()->create([
            'monitoring_target_catalog_id' => $target->id,
            'scrape_status' => 'degraded',
            'latency_ms' => 6200,
            'sample_count' => 10,
        ]);
        AlertRuleDefinition::factory()->create([
            'flow_name' => 'platform_payments',
            'rule_name' => 'payments-latency-critical',
            'severity' => 'critical',
            'version' => 'v3',
            'status' => 'applied',
            'condition_summary' => 'Latency acima do SLO critico.',
            'metadata' => [
                'metric' => 'latency_ms',
                'operator' => 'gte',
                'threshold' => 5000,
            ],
        ]);

        $evaluations = app(BackboneMonitoringInspectionService::class)->evaluateRules([
            'flow_name' => 'platform_payments',
            'publish_events' => true,
        ]);

        $this->assertCount(1, $evaluations);
        $this->assertSame('payments-latency-critical', $evaluations[0]['rule_name']);
        $this->assertSame('triggered', $evaluations[0]['alert_status']);
        $this->assertSame('prometheus-payments', $evaluations[0]['matched_targets'][0]['target_name']);

        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'MONITORAMENTO_DEGRADADO',
            'origin_context' => 'monitoring-consolidation',
        ], 'central');
    }
}
