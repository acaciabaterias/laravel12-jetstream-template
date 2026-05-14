<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\AdvancedWhiteLabelDashboard;
use App\Models\UsuarioPlataforma;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class AdvancedWhiteLabelDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_13_180000_create_central_brand_identity_profiles_table.php',
            'database/migrations/central/2026_05_13_180100_create_central_tenant_theme_versions_table.php',
            'database/migrations/central/2026_05_13_180200_create_central_theme_asset_records_table.php',
            'database/migrations/central/2026_05_13_180300_create_central_theme_publication_records_table.php',
            'database/migrations/central/2026_05_13_180400_create_central_theme_rollback_evidences_table.php',
            'database/migrations/central/2026_05_13_180450_add_cliente_id_to_white_label_configs_table.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_support_operator_can_view_the_advanced_white_label_dashboard(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $response = $this
            ->actingAs($support, 'platform')
            ->get(route('admin.branding.index'));

        $response
            ->assertOk()
            ->assertSee('Advanced white label experience')
            ->assertSeeLivewire(AdvancedWhiteLabelDashboard::class);
    }

    public function test_inactive_operator_cannot_render_the_advanced_white_label_dashboard(): void
    {
        $inactive = UsuarioPlataforma::factory()->create(['papel' => 'support', 'ativo' => false]);

        $this->actingAs($inactive, 'platform');

        Livewire::test(AdvancedWhiteLabelDashboard::class)
            ->assertForbidden();
    }
}
