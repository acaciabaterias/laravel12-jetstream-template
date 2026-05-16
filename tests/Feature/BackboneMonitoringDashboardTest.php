<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\BackboneMonitoringDashboard;
use App\Models\MonitoringProbeSnapshot;
use App\Models\MonitoringTargetCatalog;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class BackboneMonitoringDashboardTest extends TestCase
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

    public function test_support_operator_can_view_the_backbone_monitoring_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $target = MonitoringTargetCatalog::factory()->create([
            'target_name' => 'grafana-main',
            'flow_name' => 'integration_backbone',
        ]);
        MonitoringProbeSnapshot::factory()->create([
            'monitoring_target_catalog_id' => $target->id,
            'scrape_status' => 'healthy',
            'sample_count' => 25,
        ]);

        $response = $this
            ->actingAs($support, 'platform')
            ->get(route('admin.monitoring.index'));

        $response
            ->assertOk()
            ->assertSee('Monitoring readiness')
            ->assertSee('grafana-main')
            ->assertSee('Provisionamento de dashboards')
            ->assertSeeLivewire(BackboneMonitoringDashboard::class);
    }

    public function test_inactive_operator_cannot_render_the_backbone_monitoring_dashboard(): void
    {
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->actingAs($inactive, 'platform');

        Livewire::test(BackboneMonitoringDashboard::class)
            ->assertForbidden();
    }
}
