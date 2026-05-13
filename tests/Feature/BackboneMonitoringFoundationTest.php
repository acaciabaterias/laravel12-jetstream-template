<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\MonitoringTargetCatalog;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BackboneMonitoringFoundationTest extends TestCase
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

    public function test_central_monitoring_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('monitoring_target_catalogs'));
        $this->assertTrue(Schema::connection('central')->hasTable('monitoring_probe_snapshots'));
        $this->assertTrue(Schema::connection('central')->hasTable('alert_rule_definitions'));
        $this->assertTrue(Schema::connection('central')->hasTable('dashboard_provisioning_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('monitoring_readiness_evidences'));
    }

    public function test_backbone_monitoring_permissions_are_restricted_to_platform_operations_roles(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->assertTrue(Gate::forUser($support)->allows('manage-backbone-monitoring'));
        $this->assertFalse(Gate::forUser($inactive)->allows('manage-backbone-monitoring'));
    }

    public function test_monitoring_target_model_uses_central_connection(): void
    {
        $target = MonitoringTargetCatalog::factory()->create();

        $this->assertSame('central', $target->getConnectionName());
    }
}
