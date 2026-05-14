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

class AdvancedWhiteLabelRollbackInspectionTest extends TestCase
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

    public function test_it_records_rollback_evidence_with_restored_theme_reference(): void
    {
        $support = UsuarioPlataforma::factory()->create(['papel' => 'support']);
        $tenant = Cliente::factory()->create(['subdominio' => 'rollback-brand', 'status' => 'active']);
        $profile = BrandIdentityProfile::factory()->create(['cliente_id' => $tenant->id]);
        $baseline = TenantThemeVersion::factory()->create([
            'brand_identity_profile_id' => $profile->id,
            'version_label' => 'baseline',
        ]);
        $candidate = TenantThemeVersion::factory()->create([
            'brand_identity_profile_id' => $profile->id,
            'version_label' => 'candidate',
        ]);

        $publicationService = app(AdvancedWhiteLabelPublicationService::class);
        $publicationService->publish($baseline, 'production', $support->id);
        $publicationService->publish($candidate, 'production', $support->id);

        app(AdvancedWhiteLabelRollbackService::class)->rollback($candidate, 'Contraste em producao inconsistente.', $support->id);

        $response = $this
            ->actingAs($support, 'platform')
            ->getJson(route('admin.branding.inspection'));

        $response
            ->assertOk()
            ->assertJsonPath('rollbacks.0.restored_theme_version_id', $baseline->id);
    }
}
