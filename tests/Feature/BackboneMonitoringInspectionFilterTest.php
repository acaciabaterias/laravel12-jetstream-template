<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AlertRuleDefinition;
use App\Models\MonitoringProbeSnapshot;
use App\Models\MonitoringTargetCatalog;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackboneMonitoringInspectionFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
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

    public function test_monitoring_inspection_filters_by_flow_and_alert_status(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $paymentsTarget = MonitoringTargetCatalog::factory()->create([
            'flow_name' => 'platform_payments',
            'target_name' => 'payments-prometheus',
            'status' => 'unavailable',
        ]);
        MonitoringProbeSnapshot::factory()->create([
            'monitoring_target_catalog_id' => $paymentsTarget->id,
            'scrape_status' => 'unavailable',
            'latency_ms' => 0,
            'sample_count' => 0,
            'failure_reason' => 'http_status_503',
        ]);
        $recoveryTarget = MonitoringTargetCatalog::factory()->create([
            'flow_name' => 'platform_recovery',
            'target_name' => 'recovery-prometheus',
            'status' => 'healthy',
        ]);
        MonitoringProbeSnapshot::factory()->create([
            'monitoring_target_catalog_id' => $recoveryTarget->id,
            'scrape_status' => 'healthy',
            'latency_ms' => 500,
            'sample_count' => 14,
        ]);

        AlertRuleDefinition::factory()->create([
            'flow_name' => 'platform_payments',
            'rule_name' => 'payments-collector-critical',
            'severity' => 'critical',
            'status' => 'applied',
            'metadata' => [
                'metric' => 'collector_unavailable',
                'operator' => 'eq',
                'threshold' => true,
            ],
        ]);
        AlertRuleDefinition::factory()->create([
            'flow_name' => 'platform_recovery',
            'rule_name' => 'recovery-latency-warning',
            'severity' => 'warning',
            'status' => 'applied',
            'metadata' => [
                'metric' => 'latency_ms',
                'operator' => 'gte',
                'threshold' => 1500,
            ],
        ]);

        $response = $this->actingAs($support, 'platform')
            ->getJson(route('admin.monitoring.inspection', [
                'flow_name' => 'platform_payments',
                'alert_status' => 'triggered',
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'targets')
            ->assertJsonPath('targets.0.flow_name', 'platform_payments')
            ->assertJsonCount(1, 'alert_rules')
            ->assertJsonPath('alert_rules.0.rule_name', 'payments-collector-critical')
            ->assertJsonPath('alert_rules.0.alert_status', 'triggered')
            ->assertJsonPath('alert_rules.0.matched_targets.0.target_name', 'payments-prometheus');
    }
}
