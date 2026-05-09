<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\SnapshotAnalyticsComercial;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformCommercialAnalyticsFoundationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
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

    public function test_central_analytics_tables_are_available(): void
    {
        $this->assertTrue(Schema::connection('central')->hasTable('snapshots_analytics_comercial'));
        $this->assertTrue(Schema::connection('central')->hasTable('recortes_coorte_comercial'));
        $this->assertTrue(Schema::connection('central')->hasTable('metric_channel_performance'));
        $this->assertTrue(Schema::connection('central')->hasTable('insights_risco_comercial'));
        $this->assertTrue(Schema::connection('central')->hasTable('drilldowns_analytics_comercial'));
    }

    public function test_platform_commercial_analytics_permissions_are_restricted_to_billing_roles(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $support = UsuarioPlataforma::factory()->create();

        $this->assertTrue(Gate::forUser($billing)->allows('manage-platform-commercial-analytics'));
        $this->assertFalse(Gate::forUser($support)->allows('manage-platform-commercial-analytics'));
    }

    public function test_snapshot_model_uses_central_connection(): void
    {
        $snapshot = SnapshotAnalyticsComercial::factory()->create();

        $this->assertSame('central', $snapshot->getConnectionName());
    }
}
