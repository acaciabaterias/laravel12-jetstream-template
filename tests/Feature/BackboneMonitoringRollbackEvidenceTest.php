<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Operations\MonitoringProvisioningService;
use App\Services\Operations\MonitoringReadinessEvidenceService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackboneMonitoringRollbackEvidenceTest extends TestCase
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

    public function test_it_records_rollback_evidence_and_publishes_material_event(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create(['name' => 'Operador Rollback']);
        $provisioningService = app(MonitoringProvisioningService::class);
        $evidenceService = app(MonitoringReadinessEvidenceService::class);

        $record = $provisioningService->register([
            'package_name' => 'backbone-alerts',
            'version' => 'v5.1.0',
            'environment' => 'staging',
        ]);
        $record = $provisioningService->markProvisioned($record);
        $record = $provisioningService->rollback($record, [
            'rollback_version' => 'v5.0.8',
        ]);
        $evidence = $evidenceService->recordForProvisioning($record, [
            'evidence_type' => 'rollback',
            'operator_user_id' => $operator->id,
            'result_status' => 'success',
            'notes' => 'Rollback concluido e scrape revalidado.',
        ]);

        $this->assertSame('rollback', $evidence->evidence_type);
        $this->assertSame('v5.0.8', $evidence->payload['rollback_version']);
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'ROLLBACK_MONITORAMENTO_EXECUTADO',
            'origin_context' => 'monitoring-consolidation',
        ], 'central');
    }
}
