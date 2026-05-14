<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cliente;
use App\Services\Operations\AdvancedWhiteLabelBrandService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedWhiteLabelBrandIdentityTest extends TestCase
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

    public function test_it_persists_brand_identity_and_assets_per_tenant(): void
    {
        $tenant = Cliente::factory()->create([
            'subdominio' => 'brand-one',
            'status' => 'active',
        ]);

        $profile = app(AdvancedWhiteLabelBrandService::class)->registerProfile([
            'cliente_id' => $tenant->id,
            'brand_name' => 'Acacia Prime',
            'brand_slug' => 'acacia-prime',
            'login_title' => 'Acacia Prime ERP',
            'default_font_family' => 'Poppins',
            'default_theme_tokens' => [
                'primary' => '#123B66',
                'secondary' => '#F59E0B',
                'surface' => '#F8FAFC',
                'accent' => '#0F766E',
                'text' => '#0F172A',
            ],
        ], [
            'logo_primary' => 'https://example.com/logo.png',
            'favicon' => 'https://example.com/favicon.png',
        ]);

        $this->assertSame($tenant->id, $profile->cliente_id);
        $this->assertCount(2, $profile->assets);
        $this->assertDatabaseHas('brand_identity_profiles', [
            'cliente_id' => $tenant->id,
            'brand_name' => 'Acacia Prime',
        ], 'central');
        $this->assertDatabaseHas('theme_asset_records', [
            'brand_identity_profile_id' => $profile->id,
            'asset_type' => 'logo_primary',
        ], 'central');
    }
}
