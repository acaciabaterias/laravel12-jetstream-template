<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformCommercialAnalyticsDashboard;
use App\Models\SnapshotAnalyticsComercial;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformCommercialAnalyticsDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_220105_create_central_snapshot_analytics_comercials_table.php',
            'database/migrations/central/2026_05_08_220106_create_central_recorte_coorte_comercials_table.php',
            'database/migrations/central/2026_05_08_220107_create_central_metrica_performance_canals_table.php',
            'database/migrations/central/2026_05_08_220108_create_central_insight_risco_comercials_table.php',
            'database/migrations/central/2026_05_08_220109_create_central_drilldown_analytics_comercials_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_billing_operator_can_view_the_platform_commercial_analytics_dashboard(): void
    {
        $operador = UsuarioPlataforma::factory()->billing()->create();
        SnapshotAnalyticsComercial::factory()->create([
            'mrr_amount' => 999.99,
            'churn_count' => 2,
        ]);

        $response = $this
            ->actingAs($operador, 'platform')
            ->get(route('admin.analytics.index'));

        $response
            ->assertOk()
            ->assertSee('Analytics comercial da plataforma')
            ->assertSee('MRR')
            ->assertSeeLivewire(PlatformCommercialAnalyticsDashboard::class);
    }

    public function test_support_user_cannot_render_the_platform_commercial_analytics_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $this->actingAs($support, 'platform');

        Livewire::test(PlatformCommercialAnalyticsDashboard::class)
            ->assertForbidden();
    }
}
