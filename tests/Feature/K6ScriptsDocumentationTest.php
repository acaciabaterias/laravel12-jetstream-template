<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class K6ScriptsDocumentationTest extends TestCase
{
    private function projectPath(string $path = ''): string
    {
        return dirname(__DIR__, 2).($path !== '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function test_k6_scripts_exist(): void
    {
        $paths = [
            $this->projectPath('tests/k6/load-test-create-vale.js'),
            $this->projectPath('tests/k6/load-test-concurrent-users.js'),
            $this->projectPath('tests/k6/load-test-multi-tenant-dashboard.js'),
            $this->projectPath('tests/k6/smoke-test.js'),
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path);
        }
    }

    public function test_readme_documents_k6_execution(): void
    {
        $readme = file_get_contents($this->projectPath('README.md'));

        $this->assertIsString($readme);
        $this->assertStringContainsString('Testes de carga com K6', $readme);
        $this->assertStringContainsString('tests/k6/load-test-create-vale.js', $readme);
        $this->assertStringContainsString('tests/k6/load-test-concurrent-users.js', $readme);
        $this->assertStringContainsString('tests/k6/load-test-multi-tenant-dashboard.js', $readme);
        $this->assertStringContainsString('tests/k6/smoke-test.js', $readme);
        $this->assertStringContainsString('BASE_URL', $readme);
        $this->assertStringContainsString('TENANT_HOSTS', $readme);
        $this->assertStringContainsString('TENANT_PREFIX', $readme);
        $this->assertStringContainsString('TENANT_BASE_DOMAIN', $readme);
        $this->assertStringContainsString('TENANT_COUNT', $readme);
        $this->assertStringContainsString('k6 run tests/k6/smoke-test.js', $readme);
    }
}
