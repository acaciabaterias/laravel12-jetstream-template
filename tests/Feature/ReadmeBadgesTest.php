<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class ReadmeBadgesTest extends TestCase
{
    public function test_readme_contains_requested_badges(): void
    {
        $readme = file_get_contents(base_path('README.md'));

        $this->assertIsString($readme);
        $this->assertStringContainsString('Tests Passing', $readme);
        $this->assertStringContainsString('tests-202%20passed', $readme);
        $this->assertStringContainsString('PHP Version', $readme);
        $this->assertStringContainsString('Laravel Version', $readme);
        $this->assertStringContainsString('Docker Ready', $readme);
        $this->assertStringContainsString('Kubernetes Ready', $readme);
        $this->assertStringContainsString('License: MIT', $readme);
        $this->assertStringContainsString('GitHub Stars', $readme);
        $this->assertStringContainsString('acaciabaterias/laravel12-jetstream-template', $readme);
    }
}
