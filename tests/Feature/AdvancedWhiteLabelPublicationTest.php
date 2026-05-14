<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\BrandIdentityProfile;
use App\Models\Cliente;
use App\Models\TenantThemeVersion;
use App\Models\UsuarioPlataforma;
use App\Services\Operations\AdvancedWhiteLabelPublicationService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedWhiteLabelPublicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('advanced_white_label.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
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

    public function test_it_publishes_valid_theme_versions_and_blocks_invalid_ones(): void
    {
        $tenant = Cliente::factory()->create(['subdominio' => 'wl-publish', 'status' => 'active']);
        $profile = BrandIdentityProfile::factory()->create(['cliente_id' => $tenant->id]);
        $operator = UsuarioPlataforma::factory()->create(['papel' => 'support']);

        $validTheme = TenantThemeVersion::factory()->create([
            'brand_identity_profile_id' => $profile->id,
            'version_label' => 'launch',
        ]);
        $invalidTheme = TenantThemeVersion::factory()->create([
            'brand_identity_profile_id' => $profile->id,
            'version_label' => 'blocked',
            'theme_tokens' => [
                'primary' => '#FFFFFF',
                'secondary' => '#EEEEEE',
                'surface' => '#FFFFFF',
                'accent' => '#DDDDDD',
                'text' => '#F8FAFC',
            ],
        ]);

        $service = app(AdvancedWhiteLabelPublicationService::class);
        $published = $service->publish($validTheme, 'production', $operator->id);
        $rejected = $service->publish($invalidTheme, 'production', $operator->id);

        $this->assertTrue($published->validation_passed);
        $this->assertFalse($rejected->validation_passed);
        $this->assertDatabaseHas('white_label_configs', [
            'cliente_id' => $tenant->id,
            'titulo_login' => $profile->login_title,
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'TEMA_WHITE_LABEL_PUBLICADO',
            'origin_context' => 'advanced-white-label',
        ], 'central');
    }
}
