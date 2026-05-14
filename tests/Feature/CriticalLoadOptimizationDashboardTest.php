<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\CriticalLoadOptimizationDashboard;
use App\Models\BenchmarkExecutionRecord;
use App\Models\LoadScenarioProfile;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class CriticalLoadOptimizationDashboardTest extends TestCase
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

    public function test_support_operator_can_view_the_critical_load_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $scenario = LoadScenarioProfile::factory()->create([
            'scenario_name' => 'payments-batch',
            'flow_name' => 'platform_payments',
        ]);
        BenchmarkExecutionRecord::factory()->create([
            'load_scenario_profile_id' => $scenario->id,
            'comparison_status' => 'stable',
        ]);

        $response = $this
            ->actingAs($support, 'platform')
            ->get(route('admin.capacity.index'));

        $response
            ->assertOk()
            ->assertSee('Critical load optimization')
            ->assertSee('payments-batch')
            ->assertSeeLivewire(CriticalLoadOptimizationDashboard::class);
    }

    public function test_inactive_operator_cannot_render_the_critical_load_dashboard(): void
    {
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->actingAs($inactive, 'platform');

        Livewire::test(CriticalLoadOptimizationDashboard::class)
            ->assertForbidden();
    }
}
