<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Operations\OperationalIncidentService;
use App\Services\Operations\RunbookEvidenceService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionObservabilityRunbookEvidenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
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

    public function test_it_records_runbook_evidence_and_acknowledges_the_incident(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $incident = app(OperationalIncidentService::class)->open([
            'incident_key' => 'backbone-warning-1',
            'flow_name' => 'integration_backbone',
            'severity' => 'warning',
            'summary' => 'Backlog crescente no backbone.',
        ]);

        $evidence = app(RunbookEvidenceService::class)->record($incident, [
            'execution_type' => 'rollback',
            'operator_user_id' => $operator->id,
            'started_at' => now()->subMinutes(20),
            'finished_at' => now()->subMinutes(5),
            'result_status' => 'partial',
            'evidence_payload' => ['steps' => ['rollback-started', 'post-check-pending']],
            'notes' => 'Rollback parcial aguardando validacao.',
        ]);

        $this->assertSame('partial', $evidence->result_status->value);
        $this->assertSame('acknowledged', $incident->fresh()->status->value);

        $this->assertDatabaseHas('runbook_execution_evidences', [
            'id' => $evidence->id,
            'execution_type' => 'rollback',
            'result_status' => 'partial',
        ], 'central');
    }
}
