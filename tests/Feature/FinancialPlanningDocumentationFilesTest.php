<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FinancialPlanningDocumentationFilesTest extends TestCase
{
    public function test_requested_financial_planning_files_exist(): void
    {
        $files = [
            'PRICING_STRATEGY.md',
            'FINANCIAL_PROJECTIONS.md',
            'COMMISSION_MODEL.md',
            'INVOICE_TEMPLATE.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_financial_planning_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Estratégia de Precificação - ERP BateriaExpert', (string) file_get_contents(base_path('PRICING_STRATEGY.md')));
        $this->assertStringContainsString('# Projeção Financeira - ERP BateriaExpert', (string) file_get_contents(base_path('FINANCIAL_PROJECTIONS.md')));
        $this->assertStringContainsString('# Modelo de Comissão - ERP BateriaExpert', (string) file_get_contents(base_path('COMMISSION_MODEL.md')));
        $this->assertStringContainsString('# Template de Fatura - ERP BateriaExpert', (string) file_get_contents(base_path('INVOICE_TEMPLATE.md')));
    }
}
