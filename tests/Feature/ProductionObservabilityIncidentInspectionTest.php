<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Operations\OperationalIncidentService;
use App\Services\Operations\RunbookEvidenceService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionObservabilityIncidentInspectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_09_133558_create_central_operational_alert_snapshots_table.php',
            'database/migrations/central/2026_05_09_133559_create_central_load_test_baselines_table.php',
            'database/migrations/central/2026_05_09_133600_create_central_operational_incident_records_table.php',
            'database/migrations/central/2026_05_09_133601_create_central_runbook_execution_evidences_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_operational_inspection_returns_incidents_and_evidence_by_flow(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $operator = UsuarioPlataforma::factory()->billing()->create(['name' => 'Operador N1']);
        $incident = app(OperationalIncidentService::class)->open([
            'incident_key' => 'payments-critical-1',
            'flow_name' => 'platform_payments',
            'severity' => 'critical',
            'summary' => 'Falha recorrente de conciliacao.',
        ]);

        app(RunbookEvidenceService::class)->record($incident, [
            'execution_type' => 'replay',
            'operator_user_id' => $operator->id,
            'started_at' => now()->subMinutes(10),
            'finished_at' => now()->subMinutes(5),
            'result_status' => 'success',
            'evidence_payload' => ['steps' => ['replay-triggered', 'queue-drained']],
            'notes' => 'Replay executado com sucesso.',
            'metadata' => ['source' => 'feature-test'],
        ]);

        $response = $this->actingAs($support, 'platform')
            ->getJson(route('admin.operations.inspection', [
                'flow_name' => 'platform_payments',
                'incident_status' => 'acknowledged',
            ]));

        $response->assertOk()
            ->assertJsonCount(1, 'incidents')
            ->assertJsonPath('incidents.0.incident_key', 'payments-critical-1')
            ->assertJsonPath('incidents.0.status', 'acknowledged')
            ->assertJsonPath('incidents.0.evidences.0.execution_type', 'replay')
            ->assertJsonPath('incidents.0.evidences.0.operator', 'Operador N1');
    }
}
