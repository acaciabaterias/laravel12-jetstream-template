<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\FiscalCfopCatalogEntry;
use App\Models\FiscalOperationScenario;
use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;
use App\Models\FiscalTaxProfile;
use App\Models\UsuarioPlataforma;
use App\Services\Fiscal\PlatformFiscalScenarioLookupService;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalInterstateLookupTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformFiscalRuleMigrations();
    }

    public function test_lookup_resolves_interstate_tax_context_when_profile_matches_origin_destination_and_partner(): void
    {
        FiscalOperationScenario::factory()->create([
            'scenario_key' => 'interstate_resale',
            'display_name' => 'Interstate resale',
            'operation_direction' => 'domestic_out',
        ]);
        FiscalCfopCatalogEntry::factory()->create([
            'cfop_code' => '6102',
            'description' => 'Interstate resale',
            'operation_direction' => 'domestic_out',
        ]);
        $publication = FiscalRulePublicationRecord::factory()->create([
            'status' => FiscalRulePublicationStatus::Active->value,
            'release_key' => 'fiscal-interstate-001',
            'published_at' => now(),
        ]);
        $mapping = FiscalRuleMapping::factory()->create([
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => 'interstate_resale',
            'cfop_code' => '6102',
            'classification_code' => '85072010',
            'operation_direction' => 'domestic_out',
        ]);
        FiscalTaxProfile::factory()->create([
            'fiscal_rule_mapping_id' => $mapping->id,
            'fiscal_rule_publication_record_id' => $publication->id,
            'scenario_key' => 'interstate_resale',
            'cfop_code' => '6102',
            'partner_type' => 'customer',
            'operation_purpose' => 'resale',
            'origin_state' => 'SP',
            'destination_state' => 'RJ',
            'interstate_tax_rate' => 12,
            'tax_payload' => ['icms_rate' => 12, 'difal_applicable' => false],
        ]);

        $lookup = app(PlatformFiscalScenarioLookupService::class)->resolve('interstate_resale', $publication, [
            'origin_state' => 'SP',
            'destination_state' => 'RJ',
            'partner_type' => 'customer',
            'operation_purpose' => 'resale',
            'tax_regime' => 'regular',
        ]);

        $this->assertSame('active_mapping', $lookup['resolution_type']);
        $this->assertSame('6102', $lookup['cfop_code']);
        $this->assertTrue($lookup['tax_context']['is_interstate']);
        $this->assertSame('12.00', $lookup['tax_profile']['interstate_tax_rate']);

        $resolutionResponse = $this
            ->actingAs(UsuarioPlataforma::factory()->billing()->create(), 'platform')
            ->getJson(route('admin.fiscal-rules.resolve', [
                'scenario' => 'interstate_resale',
                'origin_state' => 'SP',
                'destination_state' => 'RJ',
                'partner_type' => 'customer',
                'operation_purpose' => 'resale',
                'tax_regime' => 'regular',
            ]));

        $resolutionResponse
            ->assertOk()
            ->assertJsonPath('schema_version', 'platform-fiscal-rule.v2')
            ->assertJsonPath('module_consumer', '009-fiscal-bank-orchestrator')
            ->assertJsonPath('resolution.cfop_code', '6102')
            ->assertJsonPath('tax_profile.interstate_tax_rate', '12.00');
    }
}
