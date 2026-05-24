<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Fiscal\PlatformFiscalPublicationRules;
use Tests\TestCase;

class PlatformFiscalPublicationRulesTest extends TestCase
{
    public function test_publication_rules_reject_duplicate_scenarios_and_missing_catalog_cfops(): void
    {
        $validation = app(PlatformFiscalPublicationRules::class)->validate(
            [
                ['cfop_code' => '7101', 'description' => 'Direct export', 'operation_direction' => 'export'],
            ],
            [
                ['scenario_key' => 'direct_export', 'cfop_code' => '7101', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_ncm'], 'tax_profile' => ['ncm_code' => '85072010', 'tax_regime' => 'regular', 'cst_code' => '041', 'tax_payload' => ['ipi_rate' => 0]]],
                ['scenario_key' => 'direct_export', 'cfop_code' => '9999', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_ncm'], 'tax_profile' => ['ncm_code' => '85072010', 'tax_regime' => 'regular', 'cst_code' => '041', 'tax_payload' => ['ipi_rate' => 0]]],
            ],
            [
                'invalid_mappings' => [],
            ],
        );

        $this->assertFalse($validation['passed']);
        $this->assertContains('Nao repita cenarios fiscais na mesma publicacao.', $validation['messages']);
        $this->assertContains('O cenario direct_export referencia um CFOP ausente no catalogo.', $validation['messages']);
    }

    public function test_publication_rules_reject_direction_mismatch_reported_by_coverage_snapshot(): void
    {
        $validation = app(PlatformFiscalPublicationRules::class)->validate(
            [
                ['cfop_code' => '3101', 'description' => 'Import for resale', 'operation_direction' => 'import'],
            ],
            [
                ['scenario_key' => 'direct_export', 'cfop_code' => '3101', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_ncm'], 'tax_profile' => ['ncm_code' => '85072010', 'tax_regime' => 'regular', 'cst_code' => '041', 'tax_payload' => ['ipi_rate' => 0]]],
            ],
            [
                'invalid_mappings' => [
                    ['scenario_key' => 'direct_export', 'issue_type' => 'direction_mismatch'],
                ],
            ],
        );

        $this->assertFalse($validation['passed']);
        $this->assertContains('O cenario direct_export possui direcao incompativel com a definicao governada.', $validation['messages']);
    }
}
