<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BrandIdentityProfile;
use App\Models\Cliente;
use App\Models\TenantThemeVersion;
use App\Models\UsuarioPlataforma;
use App\Services\Operations\AdvancedWhiteLabelPublicationService;
use App\Services\Operations\AdvancedWhiteLabelRollbackService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedWhiteLabelFallbackRestorationTest extends TestCase
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

    public function test_it_restores_default_branding_when_no_previous_theme_exists(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $tenant = Cliente::factory()->create(['subdominio' => 'fallback-only', 'status' => 'active']);
        $profile = BrandIdentityProfile::factory()->create([
            'cliente_id' => $tenant->id,
            'default_theme_tokens' => [
                'primary' => '#0F172A',
                'secondary' => '#F59E0B',
                'surface' => '#F8FAFC',
                'accent' => '#0F766E',
                'text' => '#111827',
            ],
        ]);
        $theme = TenantThemeVersion::factory()->create([
            'brand_identity_profile_id' => $profile->id,
            'version_label' => 'only-theme',
        ]);

        app(AdvancedWhiteLabelPublicationService::class)->publish($theme, 'production', $support->id);
        app(AdvancedWhiteLabelRollbackService::class)->rollback($theme, 'Restaurar branding padrao.', $support->id);

        $this->assertDatabaseHas('theme_rollback_evidences', [
            'tenant_theme_version_id' => $theme->id,
            'restored_theme_version_id' => null,
        ], 'central');
        $this->assertDatabaseHas('white_label_configs', [
            'cliente_id' => $tenant->id,
            'cor_primaria' => '#0F172A',
        ], 'central');
    }
}
