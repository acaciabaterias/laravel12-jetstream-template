<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class DeploymentReadinessTest extends TestCase
{
    public function test_entrypoint_runs_central_and_foundational_migrations(): void
    {
        $entrypoint = file_get_contents(base_path('entrypoint.sh'));

        $this->assertIsString($entrypoint);
        $this->assertStringContainsString('--path=database/migrations/central', $entrypoint);
        $this->assertStringContainsString('0001_01_01_000000_create_users_table.php', $entrypoint);
        $this->assertStringContainsString('0001_01_01_000001_create_cache_table.php', $entrypoint);
        $this->assertStringContainsString('0001_01_01_000002_create_jobs_table.php', $entrypoint);
        $this->assertStringContainsString('2025_08_29_233500_create_personal_access_tokens_table.php', $entrypoint);
    }

    public function test_microservice_urls_use_canonical_environment_variables_with_legacy_fallbacks(): void
    {
        $services = file_get_contents(base_path('config/services.php'));
        $environmentExample = file_get_contents(base_path('.env.example'));
        $configMap = file_get_contents(base_path('infra/kubernetes/production/configmap.yaml'));

        $this->assertIsString($services);
        $this->assertIsString($environmentExample);
        $this->assertIsString($configMap);

        $this->assertStringContainsString("env('MS_FISCAL_URL', env('MS001_BASE_URL'", $services);
        $this->assertStringContainsString("env('MS_BANCARIO_URL', env('MS002_BASE_URL'", $services);
        $this->assertStringContainsString("env('MS_WHATSAPP_URL', env('MS003_BASE_URL'", $services);
        $this->assertStringContainsString('MS_FISCAL_URL=', $environmentExample);
        $this->assertStringContainsString('MS_BANCARIO_URL=', $environmentExample);
        $this->assertStringContainsString('MS_WHATSAPP_URL=', $environmentExample);
        $this->assertStringContainsString('MS_FISCAL_URL:', $configMap);
        $this->assertStringContainsString('MS_BANCARIO_URL:', $configMap);
        $this->assertStringContainsString('MS_WHATSAPP_URL:', $configMap);
    }

    public function test_kustomization_does_not_apply_secret_examples(): void
    {
        $kustomization = file_get_contents(base_path('infra/kubernetes/production/kustomization.yaml'));

        $this->assertIsString($kustomization);
        $this->assertStringNotContainsString('secret.example.yaml', $kustomization);
        $this->assertStringNotContainsString('sealedsecret.example.yaml', $kustomization);
    }

    public function test_docker_compose_allows_overriding_erp_http_port(): void
    {
        $compose = file_get_contents(base_path('docker-compose.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('${ERP_CORE_HTTP_PORT:-8000}:80', $compose);
    }

    public function test_super_admin_seeder_rejects_placeholder_passwords_in_production(): void
    {
        $seeder = file_get_contents(base_path('database/seeders/SuperAdminSeeder.php'));

        $this->assertIsString($seeder);
        $this->assertStringContainsString("app()->environment('production')", $seeder);
        $this->assertStringContainsString('change-me-before-deploy', $seeder);
        $this->assertStringContainsString('Configure SUPER_ADMIN_PASSWORD', $seeder);
    }

    public function test_validate_env_rejects_unsafe_production_values(): void
    {
        $process = new Process(['./validate-env.sh'], base_path(), [
            'APP_NAME' => 'BateriaExpert',
            'APP_ENV' => 'production',
            'APP_KEY' => 'not-base64',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://erp.example.com',
            'DB_CONNECTION' => 'central',
            'DB_CENTRAL_DRIVER' => 'pgsql',
            'DB_CENTRAL_HOST' => 'postgres',
            'DB_CENTRAL_PORT' => '5432',
            'DB_CENTRAL_DATABASE' => 'erp_central',
            'DB_CENTRAL_USERNAME' => 'erp',
            'DB_CENTRAL_PASSWORD' => 'secret',
            'REDIS_HOST' => 'redis',
            'REDIS_PORT' => '6379',
            'CACHE_STORE' => 'redis',
            'SESSION_DRIVER' => 'database',
            'SESSION_ENCRYPT' => 'false',
            'SESSION_SECURE_COOKIE' => 'false',
            'QUEUE_CONNECTION' => 'redis',
            'SUPER_ADMIN_PASSWORD' => 'change-me-before-deploy',
            'CORS_ALLOWED_ORIGINS' => '*',
        ]);

        $process->run();

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertStringContainsString('APP_DEBUG deve ser false', $process->getOutput());
        $this->assertStringContainsString('APP_URL deve usar HTTPS', $process->getOutput());
        $this->assertStringContainsString('SUPER_ADMIN_PASSWORD deve ser uma senha forte', $process->getOutput());
        $this->assertStringContainsString('SESSION_SECURE_COOKIE deve ser true', $process->getOutput());
        $this->assertStringContainsString('SESSION_ENCRYPT deve ser true', $process->getOutput());
        $this->assertStringContainsString('CORS_ALLOWED_ORIGINS nao deve ser *', $process->getOutput());
    }

    public function test_validate_env_accepts_hardened_production_values(): void
    {
        $process = new Process(['./validate-env.sh'], base_path(), [
            'APP_NAME' => 'BateriaExpert',
            'APP_ENV' => 'production',
            'APP_KEY' => 'base64:'.str_repeat('A', 44),
            'APP_DEBUG' => 'false',
            'APP_URL' => 'https://erp.example.com',
            'DB_CONNECTION' => 'central',
            'DB_CENTRAL_DRIVER' => 'pgsql',
            'DB_CENTRAL_HOST' => 'postgres',
            'DB_CENTRAL_PORT' => '5432',
            'DB_CENTRAL_DATABASE' => 'erp_central',
            'DB_CENTRAL_USERNAME' => 'erp',
            'DB_CENTRAL_PASSWORD' => 'secret',
            'REDIS_HOST' => 'redis',
            'REDIS_PORT' => '6379',
            'CACHE_STORE' => 'redis',
            'SESSION_DRIVER' => 'database',
            'SESSION_ENCRYPT' => 'true',
            'SESSION_SECURE_COOKIE' => 'true',
            'QUEUE_CONNECTION' => 'redis',
            'SUPER_ADMIN_PASSWORD' => 'Senha-Forte-Para-Producao-2026',
            'CORS_ALLOWED_ORIGINS' => 'https://erp.example.com',
        ]);

        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
        $this->assertStringContainsString('Validando guardrails de producao', $process->getOutput());
    }
}
