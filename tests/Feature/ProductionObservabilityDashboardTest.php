<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ProductionObservabilityDashboard;
use App\Models\OperationalAlertSnapshot;
use App\Models\OperationalIncidentRecord;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class ProductionObservabilityDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_09_133557_create_central_operational_slo_definitions_table.php',
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

    public function test_support_operator_can_view_the_operational_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        OperationalAlertSnapshot::factory()->create([
            'flow_name' => 'integration_backbone',
            'severity' => 'warning',
        ]);

        $response = $this
            ->actingAs($support, 'platform')
            ->get(route('admin.operations.index'));

        $response
            ->assertOk()
            ->assertSee('Observabilidade operacional')
            ->assertSeeLivewire(ProductionObservabilityDashboard::class);
    }

    public function test_inactive_operator_cannot_render_the_operational_dashboard(): void
    {
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->actingAs($inactive, 'platform');

        Livewire::test(ProductionObservabilityDashboard::class)
            ->assertForbidden();
    }

    public function test_support_operator_can_manage_incident_lifecycle_from_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $incident = OperationalIncidentRecord::factory()->create([
            'status' => 'open',
            'summary' => 'Fila de pagamentos degradada.',
        ]);

        $this->actingAs($support, 'platform');

        Livewire::test(ProductionObservabilityDashboard::class)
            ->call('acknowledgeIncident', $incident->id)
            ->assertSet('operationMessage', sprintf('Incidente %d reconhecido com sucesso.', $incident->id))
            ->set('selectedIncidentId', (string) $incident->id)
            ->set('incidentExecutionType', 'replay')
            ->set('incidentResultStatus', 'success')
            ->set('incidentValidationChecks', 'queue-drained,reconciliation-ok')
            ->set('incidentNotes', 'Replay executado e validado.')
            ->call('recordRunbookEvidence')
            ->assertSet('operationMessage', sprintf('Evidencia operacional registrada no incidente %d.', $incident->id))
            ->call('resolveIncident', $incident->id)
            ->assertSet('operationMessage', sprintf('Incidente %d marcado como resolvido.', $incident->id))
            ->call('closeIncident')
            ->assertSet('operationMessage', sprintf('Incidente %d encerrado com validacao registrada.', $incident->id));

        $this->assertSame('closed', $incident->fresh()->status->value);
        $this->assertDatabaseHas('runbook_execution_evidences', [
            'operational_incident_record_id' => $incident->id,
            'execution_type' => 'replay',
            'result_status' => 'success',
        ], 'central');
    }
}
