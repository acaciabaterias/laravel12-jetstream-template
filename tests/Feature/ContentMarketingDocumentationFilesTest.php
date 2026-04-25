<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ContentMarketingDocumentationFilesTest extends TestCase
{
    public function test_requested_content_marketing_files_exist(): void
    {
        $files = [
            'SEO_ARTICLES.md',
            'EMAIL_SEQUENCE.md',
            'SOCIAL_MEDIA_CALENDAR.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_content_marketing_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Artigos SEO - ERP BateriaExpert', (string) file_get_contents(base_path('SEO_ARTICLES.md')));
        $this->assertStringContainsString('# Sequência de E-mails para Leads - ERP BateriaExpert', (string) file_get_contents(base_path('EMAIL_SEQUENCE.md')));
        $this->assertStringContainsString('# Calendário de Social Media - 30 Dias - ERP BateriaExpert', (string) file_get_contents(base_path('SOCIAL_MEDIA_CALENDAR.md')));
    }
}
