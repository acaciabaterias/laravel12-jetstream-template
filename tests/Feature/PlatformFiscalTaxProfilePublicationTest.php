<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Fiscal\PlatformFiscalPublicationService;
use RuntimeException;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalTaxProfilePublicationTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->runPlatformFiscalRuleMigrations();
    }

    public function test_publication_is_blocked_when_tax_profile_metadata_is_incomplete(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('O cenario direct_export exige NCM de referencia no perfil tributario.');

        app(PlatformFiscalPublicationService::class)->publish(
            [
                ['cfop_code' => '7101', 'description' => 'Direct export of own production', 'operation_direction' => 'export'],
                ['cfop_code' => '7501', 'description' => 'Indirect export remittance', 'operation_direction' => 'export'],
                ['cfop_code' => '3101', 'description' => 'Import for resale', 'operation_direction' => 'import'],
                ['cfop_code' => '3551', 'description' => 'Import for industrialization', 'operation_direction' => 'import'],
            ],
            [
                ['scenario_key' => 'direct_export', 'cfop_code' => '7101', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_ncm'], 'tax_profile' => ['tax_regime' => 'regular', 'cst_code' => '041', 'tax_payload' => ['ipi_rate' => 0]]],
                ['scenario_key' => 'indirect_export', 'cfop_code' => '7501', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_export_commitment'], 'tax_profile' => ['ncm_code' => '85072010', 'tax_regime' => 'regular', 'cst_code' => '041', 'tax_payload' => ['ipi_rate' => 0]]],
                ['scenario_key' => 'resale_import', 'cfop_code' => '3101', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_customs_record'], 'tax_profile' => ['ncm_code' => '85072010', 'tax_regime' => 'regular', 'cst_code' => '040', 'tax_payload' => ['ii_rate' => 14]]],
                ['scenario_key' => 'industrial_import', 'cfop_code' => '3551', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_ncm'], 'tax_profile' => ['ncm_code' => '85072010', 'tax_regime' => 'regular', 'cst_code' => '000', 'tax_payload' => ['ii_rate' => 14, 'ipi_rate' => 5]]],
            ],
            $billing->id,
        );
    }
}
