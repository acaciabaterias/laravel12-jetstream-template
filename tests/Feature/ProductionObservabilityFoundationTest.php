<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\OperationalAlertSnapshot;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductionObservabilityFoundationTest extends TestCase
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

    public function test_central_operational_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('operational_slo_definitions'));
        $this->assertTrue(Schema::connection('central')->hasTable('operational_alert_snapshots'));
        $this->assertTrue(Schema::connection('central')->hasTable('load_test_baselines'));
        $this->assertTrue(Schema::connection('central')->hasTable('operational_incident_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('runbook_execution_evidences'));
    }

    public function test_production_observability_permissions_are_restricted_to_platform_operations_roles(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->assertTrue(Gate::forUser($support)->allows('manage-production-observability'));
        $this->assertFalse(Gate::forUser($inactive)->allows('manage-production-observability'));
    }

    public function test_operational_snapshot_model_uses_central_connection(): void
    {
        $snapshot = OperationalAlertSnapshot::factory()->create();

        $this->assertSame('central', $snapshot->getConnectionName());
    }
}
