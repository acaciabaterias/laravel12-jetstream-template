<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformFiscalRuleManager;
use App\Models\FiscalCfopCatalogEntry;
use App\Models\FiscalOperationScenario;
use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;
use App\Models\FiscalTaxProfile;
use App\Models\UsuarioPlataforma;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalScenarioLookupTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformFiscalRuleMigrations();
    }

    public function test_billing_operator_can_consult_an_active_fiscal_mapping_for_a_required_scenario(): void
    {
        FiscalOperationScenario::factory()->create([
            'scenario_key' => 'direct_export',
            'display_name' => 'Direct export',
            'operation_direction' => 'export',
        ]);
        FiscalCfopCatalogEntry::factory()->create([
            'cfop_code' => '7101',
            'description' => 'Export sale of own production',
            'operation_direction' => 'export',
        ]);
        $publication = FiscalRulePublicationRecord::factory()->create([
            'status' => FiscalRulePublicationStatus::Active->value,
            'release_key' => 'fiscal-lookup-001',
            'published_at' => now(),
        ]);
        $mapping = FiscalRuleMapping::factory()->create([
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
            'classification_code' => '85072010',
            'operation_direction' => 'export',
            'validation_flags' => [
                'requires_ncm' => true,
                'requires_foreign_partner' => true,
            ],
        ]);
        FiscalTaxProfile::factory()->create([
            'fiscal_rule_mapping_id' => $mapping->id,
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
            'ncm_code' => '85072010',
        ]);

        $operator = UsuarioPlataforma::factory()->billing()->create();

        $response = $this
            ->actingAs($operator, 'platform')
            ->get(route('admin.fiscal-rules.index', ['scenario' => 'direct_export']));

        $response
            ->assertOk()
            ->assertSee('Fiscal CFOP governance')
            ->assertSee('7101')
            ->assertSee('active_mapping')
            ->assertSeeLivewire(PlatformFiscalRuleManager::class);

        $inspectionResponse = $this
            ->actingAs($operator, 'platform')
            ->getJson(route('admin.fiscal-rules.inspection', ['scenario' => 'direct_export']));

        $inspectionResponse
            ->assertOk()
            ->assertJsonPath('lookup.cfop_code', '7101')
            ->assertJsonPath('lookup.classification_code', '85072010')
            ->assertJsonPath('lookup.resolution_type', 'active_mapping')
            ->assertJsonPath('lookup.tax_profile.ncm_code', '85072010');
    }
}
