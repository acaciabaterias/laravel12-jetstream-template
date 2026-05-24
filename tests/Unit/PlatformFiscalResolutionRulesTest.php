<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FiscalRuleMapping;
use App\Models\FiscalTaxProfile;
use App\Services\Fiscal\PlatformFiscalResolutionRules;
use Tests\TestCase;

class PlatformFiscalResolutionRulesTest extends TestCase
{
    public function test_mapping_compatibility_requires_the_same_operation_direction(): void
    {
        $rules = app(PlatformFiscalResolutionRules::class);
        $mapping = new FiscalRuleMapping([
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
            'operation_direction' => 'export',
            'validation_flags' => ['requires_ncm' => true],
        ]);
        $mapping->setRelation('taxProfile', new FiscalTaxProfile([
            'ncm_code' => '85072010',
            'tax_regime' => 'regular',
            'cst_code' => '041',
            'tax_payload' => ['ipi_rate' => 0],
        ]));

        $this->assertTrue($rules->mappingMatchesScenario([
            'scenario_key' => 'direct_export',
            'display_name' => 'Direct export',
            'operation_direction' => 'export',
        ], $mapping));

        $this->assertFalse($rules->mappingMatchesScenario([
            'scenario_key' => 'resale_import',
            'display_name' => 'Resale import',
            'operation_direction' => 'import',
        ], $mapping));
    }

    public function test_fallback_payload_marks_missing_active_publication_for_a_required_scenario(): void
    {
        $rules = app(PlatformFiscalResolutionRules::class);

        $fallback = $rules->fallbackForScenario([
            'scenario_key' => 'direct_export',
            'display_name' => 'Direct export',
            'operation_direction' => 'export',
        ], null);

        $this->assertSame('governed_fallback', $fallback['resolution_type']);
        $this->assertSame('7101', $fallback['cfop_code']);
        $this->assertSame('missing_active_publication', $fallback['issue']['code']);
        $this->assertContains('requires_manual_review', $fallback['validation_flags']);
        $this->assertSame('regular', $fallback['tax_profile']['tax_regime']);
    }
}
