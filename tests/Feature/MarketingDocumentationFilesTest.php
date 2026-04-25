<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MarketingDocumentationFilesTest extends TestCase
{
    public function test_requested_marketing_files_exist(): void
    {
        $files = [
            'SOCIAL_MEDIA_POSTS.md',
            'PRESS_KIT.md',
            'CUSTOMER_SUCCESS_TEMPLATE.md',
            'WEBINAR_SCRIPT.md',
            'RELEASE_VIDEO_SCRIPT.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_marketing_files_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Materiais de Social Media - ERP BateriaExpert', (string) file_get_contents(base_path('SOCIAL_MEDIA_POSTS.md')));
        $this->assertStringContainsString('# Press Kit - ERP BateriaExpert', (string) file_get_contents(base_path('PRESS_KIT.md')));
        $this->assertStringContainsString('# Template de Case de Sucesso - ERP BateriaExpert', (string) file_get_contents(base_path('CUSTOMER_SUCCESS_TEMPLATE.md')));
        $this->assertStringContainsString('# Roteiro de Webinar - Lançamento ERP BateriaExpert', (string) file_get_contents(base_path('WEBINAR_SCRIPT.md')));
        $this->assertStringContainsString('# Roteiro de Vídeo de Lançamento - ERP BateriaExpert', (string) file_get_contents(base_path('RELEASE_VIDEO_SCRIPT.md')));
    }
}
