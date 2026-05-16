<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class K6ScriptsDocumentationTest extends TestCase
{
    public function test_k6_scripts_exist(): void
    {
        $paths = [
            base_path('tests/k6/load-test-create-vale.js'),
            base_path('tests/k6/load-test-concurrent-users.js'),
            base_path('tests/k6/smoke-test.js'),
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path);
        }
    }

    public function test_readme_documents_k6_execution(): void
    {
        $readme = file_get_contents(base_path('README.md'));

        $this->assertIsString($readme);
        $this->assertStringContainsString('Testes de carga com K6', $readme);
        $this->assertStringContainsString('tests/k6/load-test-create-vale.js', $readme);
        $this->assertStringContainsString('tests/k6/load-test-concurrent-users.js', $readme);
        $this->assertStringContainsString('tests/k6/smoke-test.js', $readme);
        $this->assertStringContainsString('BASE_URL', $readme);
        $this->assertStringContainsString('k6 run tests/k6/smoke-test.js', $readme);
    }
}
