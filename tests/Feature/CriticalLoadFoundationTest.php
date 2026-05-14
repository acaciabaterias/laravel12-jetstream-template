<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\LoadScenarioProfile;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CriticalLoadFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_13_170000_create_central_load_scenario_profiles_table.php',
            'database/migrations/central/2026_05_13_170100_create_central_benchmark_execution_records_table.php',
            'database/migrations/central/2026_05_13_170200_create_central_performance_bottleneck_records_table.php',
            'database/migrations/central/2026_05_13_170300_create_central_tuning_change_records_table.php',
            'database/migrations/central/2026_05_13_170400_create_central_performance_rollback_evidences_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_central_critical_load_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('load_scenario_profiles'));
        $this->assertTrue(Schema::connection('central')->hasTable('benchmark_execution_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('performance_bottleneck_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('tuning_change_records'));
        $this->assertTrue(Schema::connection('central')->hasTable('performance_rollback_evidences'));
    }

    public function test_critical_load_permissions_are_restricted_to_platform_operations_roles(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->assertTrue(Gate::forUser($support)->allows('manage-critical-load-optimization'));
        $this->assertFalse(Gate::forUser($inactive)->allows('manage-critical-load-optimization'));
    }

    public function test_load_scenario_model_uses_central_connection(): void
    {
        $scenario = LoadScenarioProfile::factory()->create();

        $this->assertSame('central', $scenario->getConnectionName());
    }
}
