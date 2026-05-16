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

class AdvancedWhiteLabelInspectionFilterTest extends TestCase
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

    public function test_it_filters_branding_inspection_by_tenant_and_publication_status(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $tenantA = Cliente::factory()->create(['subdominio' => 'tenant-a', 'status' => 'active']);
        $tenantB = Cliente::factory()->create(['subdominio' => 'tenant-b', 'status' => 'active']);
        $profileA = BrandIdentityProfile::factory()->create(['cliente_id' => $tenantA->id]);
        $profileB = BrandIdentityProfile::factory()->create(['cliente_id' => $tenantB->id]);
        $themeA = TenantThemeVersion::factory()->create(['brand_identity_profile_id' => $profileA->id, 'version_label' => 'a1']);
        $themeB = TenantThemeVersion::factory()->create([
            'brand_identity_profile_id' => $profileB->id,
            'version_label' => 'b1',
            'theme_tokens' => [
                'primary' => '#FFFFFF',
                'secondary' => '#EEEEEE',
                'surface' => '#FFFFFF',
                'accent' => '#DDDDDD',
                'text' => '#F8FAFC',
            ],
        ]);

        $service = app(AdvancedWhiteLabelPublicationService::class);
        $service->publish($themeA, 'staging', $support->id);
        $service->publish($themeB, 'staging', $support->id);

        $response = $this
            ->actingAs($support, 'platform')
            ->getJson(route('admin.branding.inspection', [
                'tenant_id' => $tenantA->id,
                'publication_status' => 'published',
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('publications.0.tenant_subdomain', 'tenant-a');
    }
}
