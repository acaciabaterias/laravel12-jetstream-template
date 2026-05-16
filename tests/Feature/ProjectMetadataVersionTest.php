<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProjectMetadataVersionTest extends TestCase
{
    public function test_stack_documentation_matches_installed_major_versions(): void
    {
        $composer = json_decode(file_get_contents(base_path('composer.json')), true);
        $package = json_decode(file_get_contents(base_path('package.json')), true);
        $readme = file_get_contents(base_path('README.md'));
        $agents = file_get_contents(base_path('AGENTS.md'));

        $this->assertIsArray($composer);
        $this->assertIsArray($package);
        $this->assertIsString($readme);
        $this->assertIsString($agents);

        $this->assertStringStartsWith('^4.', $composer['require']['livewire/livewire']);
        $this->assertStringStartsWith('^4.', $package['devDependencies']['tailwindcss']);

        $this->assertStringContainsString('Livewire `4`', $readme);
        $this->assertStringContainsString('Tailwind CSS `4`', $readme);
        $this->assertStringContainsString('livewire/livewire (LIVEWIRE) - v4', $agents);
        $this->assertStringContainsString('tailwindcss (TAILWINDCSS) - v4', $agents);
    }
}
