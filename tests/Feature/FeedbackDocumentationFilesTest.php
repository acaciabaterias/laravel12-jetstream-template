<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class FeedbackDocumentationFilesTest extends TestCase
{
    public function test_requested_feedback_files_exist(): void
    {
        $files = [
            'NPS_TEMPLATE.md',
            'FEEDBACK_FORM.md',
            'CHANGELOG_VISUAL.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_feedback_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Template de Pesquisa NPS - ERP BateriaExpert', (string) file_get_contents(base_path('NPS_TEMPLATE.md')));
        $this->assertStringContainsString('# Formulário de Feedback e Sugestões - ERP BateriaExpert', (string) file_get_contents(base_path('FEEDBACK_FORM.md')));
        $this->assertStringContainsString('# Changelog Visual - ERP BateriaExpert', (string) file_get_contents(base_path('CHANGELOG_VISUAL.md')));
    }
}
