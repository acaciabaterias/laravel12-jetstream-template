<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CommercialDocumentationFilesTest extends TestCase
{
    public function test_requested_commercial_and_training_files_exist(): void
    {
        $files = [
            'LANDING_PAGE.md',
            'PROPOSAL_TEMPLATE.md',
            'TRAINING_SLIDES.md',
            'KNOWLEDGE_BASE.md',
            'EMAIL_TEMPLATES.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_commercial_and_training_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Landing Page - ERP BateriaExpert', (string) file_get_contents(base_path('LANDING_PAGE.md')));
        $this->assertStringContainsString('# Proposta Comercial - ERP BateriaExpert', (string) file_get_contents(base_path('PROPOSAL_TEMPLATE.md')));
        $this->assertStringContainsString('# Roteiro de Treinamento - ERP BateriaExpert', (string) file_get_contents(base_path('TRAINING_SLIDES.md')));
        $this->assertStringContainsString('# Base de Conhecimento - ERP BateriaExpert', (string) file_get_contents(base_path('KNOWLEDGE_BASE.md')));
        $this->assertStringContainsString('# Modelos de E-mail para Suporte - ERP BateriaExpert', (string) file_get_contents(base_path('EMAIL_TEMPLATES.md')));
    }
}
