<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Operations\MonitoringProvisioningService;
use App\Services\Operations\MonitoringReadinessEvidenceService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackboneMonitoringProvisioningInspectionTest extends TestCase
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

    public function test_monitoring_inspection_returns_provisioning_records_and_evidence_by_environment(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $operator = UsuarioPlataforma::factory()->billing()->create(['name' => 'Operador Monitoring']);
        $provisioningService = app(MonitoringProvisioningService::class);
        $evidenceService = app(MonitoringReadinessEvidenceService::class);

        $record = $provisioningService->register([
            'package_name' => 'ops-overview',
            'version' => 'v2.4.0',
            'environment' => 'production',
        ]);
        $record = $provisioningService->markProvisioned($record);
        $record = $provisioningService->markValidated($record);
        $evidenceService->recordForProvisioning($record, [
            'evidence_type' => 'validation',
            'operator_user_id' => $operator->id,
            'result_status' => 'success',
            'notes' => 'Grafana renderizado e alert rules sincronizadas.',
        ]);

        $response = $this->actingAs($support, 'platform')
            ->getJson(route('admin.monitoring.inspection', [
                'environment' => 'production',
                'provisioning_status' => 'applied',
                'evidence_type' => 'validation',
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'provisioning_records')
            ->assertJsonPath('provisioning_records.0.package_name', 'ops-overview')
            ->assertJsonPath('provisioning_records.0.environment', 'production')
            ->assertJsonCount(1, 'readiness_evidences')
            ->assertJsonPath('readiness_evidences.0.evidence_type', 'validation')
            ->assertJsonPath('readiness_evidences.0.operator', 'Operador Monitoring');
    }
}
