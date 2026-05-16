<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class InternalOperationsDocumentationFilesTest extends TestCase
{
    public function test_requested_internal_operations_files_exist(): void
    {
        $files = [
            'INTERNAL_POLICIES.md',
            'CODE_OF_ETHICS.md',
            'REMOTE_WORK_POLICY.md',
            'HR_MANUAL.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_internal_operations_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Políticas Internas - Uso de Sistemas e IA', (string) file_get_contents(base_path('INTERNAL_POLICIES.md')));
        $this->assertStringContainsString('# Código de Ética - BateriaExpert', (string) file_get_contents(base_path('CODE_OF_ETHICS.md')));
        $this->assertStringContainsString('# Política de Trabalho Remoto - BateriaExpert', (string) file_get_contents(base_path('REMOTE_WORK_POLICY.md')));
        $this->assertStringContainsString('# Manual Básico de RH - BateriaExpert', (string) file_get_contents(base_path('HR_MANUAL.md')));
    }
}
