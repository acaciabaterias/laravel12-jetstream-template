<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class LaunchMaterialsDocumentationTest extends TestCase
{
    public function test_requested_launch_materials_exist(): void
    {
        $files = [
            'RELEASE_NOTES_v1.0.0.md',
            'LAUNCH_EMAIL.md',
            'SOCIAL_POSTS.md',
            'DEMO_SCRIPT.md',
        ];

        foreach ($files as $file) {
            $this->assertFileExists(base_path($file));
        }
    }

    public function test_requested_launch_materials_have_expected_titles(): void
    {
        $this->assertStringContainsString('# Release Notes v1.0.0 - ERP BateriaExpert', (string) file_get_contents(base_path('RELEASE_NOTES_v1.0.0.md')));
        $this->assertStringContainsString('# E-mail de Lancamento - ERP BateriaExpert', (string) file_get_contents(base_path('LAUNCH_EMAIL.md')));
        $this->assertStringContainsString('# Posts Sociais - Lancamento ERP BateriaExpert', (string) file_get_contents(base_path('SOCIAL_POSTS.md')));
        $this->assertStringContainsString('# Roteiro de Demonstracao - ERP BateriaExpert', (string) file_get_contents(base_path('DEMO_SCRIPT.md')));
    }
}
