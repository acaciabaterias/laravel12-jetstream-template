<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\OperationalIncidentRecord;
use App\Models\RunbookExecutionEvidence;
use App\Services\Operations\OperationalIncidentService;
use App\Support\Operations\OperationalIncidentStatus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionObservabilityIncidentRulesTest extends TestCase
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

    public function test_incident_cannot_close_without_completed_evidence_and_post_validation(): void
    {
        $service = app(OperationalIncidentService::class);

        $incident = OperationalIncidentRecord::factory()->create([
            'status' => OperationalIncidentStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('completed runbook evidence');

        $service->close($incident, [
            'post_validation_passed' => true,
        ]);
    }

    public function test_incident_can_close_after_resolution_completed_evidence_and_post_validation(): void
    {
        $service = app(OperationalIncidentService::class);

        $incident = OperationalIncidentRecord::factory()->create([
            'status' => OperationalIncidentStatus::Resolved->value,
            'resolved_at' => now(),
        ]);

        RunbookExecutionEvidence::factory()->create([
            'operational_incident_record_id' => $incident->id,
            'result_status' => 'success',
        ]);

        $closed = $service->close($incident, [
            'post_validation_passed' => true,
            'validated_checks' => ['backlog_normalized', 'replay_queue_empty'],
        ]);

        $this->assertSame('closed', $closed->status->value);
        $this->assertTrue($closed->metadata['closure_validation']['post_validation_passed']);
    }
}
