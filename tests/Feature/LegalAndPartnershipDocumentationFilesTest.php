<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LegalAndPartnershipDocumentationFilesTest extends TestCase
{
    public function test_requested_legal_and_partnership_files_exist(): void
    {
        $files = [
            'PARTNER_CONTRACT.md',
            'AFFILIATE_PROGRAM.md',
            'NDA_TEMPLATE.md',
            'IMAGE_USE_TERMS.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_legal_and_partnership_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Contrato de Parceria / Revenda - ERP BateriaExpert', (string) file_get_contents(base_path('PARTNER_CONTRACT.md')));
        $this->assertStringContainsString('# Programa de Afiliados - ERP BateriaExpert', (string) file_get_contents(base_path('AFFILIATE_PROGRAM.md')));
        $this->assertStringContainsString('# Acordo de Confidencialidade (NDA) - ERP BateriaExpert', (string) file_get_contents(base_path('NDA_TEMPLATE.md')));
        $this->assertStringContainsString('# Termos de Uso de Imagem - ERP BateriaExpert', (string) file_get_contents(base_path('IMAGE_USE_TERMS.md')));
    }
}
