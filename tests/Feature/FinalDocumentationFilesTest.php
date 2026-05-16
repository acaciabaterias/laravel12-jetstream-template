<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FinalDocumentationFilesTest extends TestCase
{
    public function test_requested_final_documentation_files_exist(): void
    {
        $files = [
            'USER_GUIDE.md',
            'ADMIN_GUIDE.md',
            'TERMS_OF_SERVICE.md',
            'PRIVACY_POLICY.md',
            'CONTRACT_TEMPLATE.md',
            'ONBOARDING_CHECKLIST.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_final_documentation_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Guia do Usuário Final - ERP BateriaExpert', (string) file_get_contents(base_path('USER_GUIDE.md')));
        $this->assertStringContainsString('# Guia do Administrador - ERP BateriaExpert', (string) file_get_contents(base_path('ADMIN_GUIDE.md')));
        $this->assertStringContainsString('# Termos de Serviço - ERP BateriaExpert', (string) file_get_contents(base_path('TERMS_OF_SERVICE.md')));
        $this->assertStringContainsString('# Política de Privacidade - ERP BateriaExpert', (string) file_get_contents(base_path('PRIVACY_POLICY.md')));
        $this->assertStringContainsString('# CONTRATO DE ASSINATURA - ERP BateriaExpert', (string) file_get_contents(base_path('CONTRACT_TEMPLATE.md')));
        $this->assertStringContainsString('# Checklist de Onboarding - Novos Clientes ERP BateriaExpert', (string) file_get_contents(base_path('ONBOARDING_CHECKLIST.md')));
    }
}
