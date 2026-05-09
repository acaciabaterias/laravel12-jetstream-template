<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\ProductionObservabilityDashboard;
use App\Models\OperationalAlertSnapshot;
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
}
