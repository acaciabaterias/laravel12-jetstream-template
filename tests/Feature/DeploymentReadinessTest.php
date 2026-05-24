<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class DeploymentReadinessTest extends TestCase
{
    private function projectPath(string $path = ''): string
    {
        return dirname(__DIR__, 2).($path !== '' ? DIRECTORY_SEPARATOR.$path : '');
    }

    public function test_entrypoint_runs_central_and_foundational_migrations(): void
    {
        $entrypoint = file_get_contents($this->projectPath('entrypoint.sh'));

        $this->assertIsString($entrypoint);
        $this->assertStringContainsString('--path=database/migrations/central', $entrypoint);
        $this->assertStringContainsString('0001_01_01_000000_create_users_table.php', $entrypoint);
        $this->assertStringContainsString('0001_01_01_000001_create_cache_table.php', $entrypoint);
        $this->assertStringContainsString('0001_01_01_000002_create_jobs_table.php', $entrypoint);
        $this->assertStringContainsString('2025_08_29_233500_create_personal_access_tokens_table.php', $entrypoint);
    }

    public function test_dockerfile_uses_stable_alpine_base_and_retries_package_install(): void
    {
        $dockerfile = file_get_contents($this->projectPath('Dockerfile'));

        $this->assertIsString($dockerfile);
        $this->assertStringContainsString('FROM php:8.3-fpm-alpine3.22', $dockerfile);
        $this->assertStringContainsString('for attempt in 1 2 3', $dockerfile);
        $this->assertStringContainsString('apk add --no-cache', $dockerfile);
        $this->assertStringContainsString('icu-dev', $dockerfile);
        $this->assertStringContainsString('docker-php-ext-install pdo_pgsql bcmath intl mbstring xml zip pcntl', $dockerfile);
    }

    public function test_composer_platform_is_pinned_to_production_php_version(): void
    {
        $composer = json_decode((string) file_get_contents($this->projectPath('composer.json')), true);

        $this->assertIsArray($composer);
        $this->assertSame('8.3.30', $composer['config']['platform']['php'] ?? null);
    }

    public function test_microservice_urls_use_canonical_environment_variables_with_legacy_fallbacks(): void
    {
        $services = file_get_contents($this->projectPath('config/services.php'));
        $environmentExample = file_get_contents($this->projectPath('.env.example'));
        $configMap = file_get_contents($this->projectPath('infra/kubernetes/production/configmap.yaml'));

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
        $kustomization = file_get_contents($this->projectPath('infra/kubernetes/production/kustomization.yaml'));

        $this->assertIsString($kustomization);
        $this->assertStringNotContainsString('secret.example.yaml', $kustomization);
        $this->assertStringNotContainsString('sealedsecret.example.yaml', $kustomization);
    }

    public function test_docker_compose_allows_overriding_erp_http_port(): void
    {
        $compose = file_get_contents($this->projectPath('docker-compose.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('erp_core_app:', $compose);
        $this->assertStringContainsString('image: ${ERP_CORE_NGINX_IMAGE:-bateriaexpert/erp-nginx:latest}', $compose);
        $this->assertStringContainsString('erp_core_php_fpm:', $compose);
        $this->assertStringContainsString('image: ${ERP_CORE_PHP_FPM_IMAGE:-bateriaexpert/erp-php-fpm:latest}', $compose);
        $this->assertStringContainsString('published: 8000', $compose);
    }

    public function test_docker_compose_uses_versioned_env_examples_with_optional_local_overrides(): void
    {
        $compose = file_get_contents($this->projectPath('docker-compose.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('- path: ./.env.example', $compose);
        $this->assertStringContainsString('- path: ./.env', $compose);
        $this->assertStringContainsString('required: false', $compose);
        $this->assertStringContainsString('- path: ./microservicos/ms-001-fiscal-acbr/.env.example', $compose);
        $this->assertStringContainsString('- path: ./microservicos/ms-005-geocoding/.env.example', $compose);
    }

    public function test_docker_compose_preserves_built_runtime_artifacts_when_mounting_source(): void
    {
        $compose = file_get_contents($this->projectPath('docker-compose.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('erp_core_storage:/var/www/html/storage', $compose);
        $this->assertStringNotContainsString('erp_core_public_build:/var/www/html/public/build', $compose);
    }

    public function test_docker_compose_builds_with_host_network_for_local_package_resolution(): void
    {
        $compose = file_get_contents($this->projectPath('docker-compose.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('network_swarm_public', $compose);
        $this->assertStringContainsString('configs:', $compose);
        $this->assertStringContainsString('erp_core_nginx_default_v3', $compose);
        $this->assertStringContainsString('erp_core_php_fpm_pool_v3', $compose);
        $this->assertStringContainsString("erp_core_queue:\n", $compose);
        $this->assertStringContainsString("healthcheck:\n      disable: true", $compose);
        $this->assertStringContainsString("erp_core_scheduler:\n", $compose);
    }

    public function test_swarm_runtime_images_and_configs_are_present(): void
    {
        $phpFpmDockerfile = file_get_contents($this->projectPath('Dockerfile.php-fpm'));
        $nginxDockerfile = file_get_contents($this->projectPath('Dockerfile.nginx'));
        $phpFpmConfig = file_get_contents($this->projectPath('docker/php/www.swarm.conf'));
        $nginxConfig = file_get_contents($this->projectPath('docker/nginx/default.swarm.conf'));

        $this->assertIsString($phpFpmDockerfile);
        $this->assertIsString($nginxDockerfile);
        $this->assertIsString($phpFpmConfig);
        $this->assertIsString($nginxConfig);

        $this->assertStringContainsString('FROM php:8.3-fpm-bookworm', $phpFpmDockerfile);
        $this->assertStringContainsString('CMD ["php-fpm", "-F"]', $phpFpmDockerfile);
        $this->assertStringContainsString('FROM nginx:1.27-alpine', $nginxDockerfile);
        $this->assertStringContainsString('fastcgi_pass erp-php-fpm:9000;', $nginxConfig);
        $this->assertStringContainsString('listen = 0.0.0.0:9000', $phpFpmConfig);
    }

    public function test_super_admin_seeder_rejects_placeholder_passwords_in_production(): void
    {
        $seeder = file_get_contents($this->projectPath('database/seeders/SuperAdminSeeder.php'));

        $this->assertIsString($seeder);
        $this->assertStringContainsString("app()->environment('production')", $seeder);
        $this->assertStringContainsString('change-me-before-deploy', $seeder);
        $this->assertStringContainsString('Configure SUPER_ADMIN_PASSWORD', $seeder);
    }

    public function test_validate_env_rejects_unsafe_production_values(): void
    {
        $process = new Process(['./validate-env.sh'], $this->projectPath(), [
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

    public function test_validate_env_rejects_missing_production_cors_origins(): void
    {
        $process = new Process(['./validate-env.sh'], $this->projectPath(), [
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
            'CORS_ALLOWED_ORIGINS' => '',
        ]);

        $process->run();

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertStringContainsString('CORS_ALLOWED_ORIGINS nao deve ser *', $process->getOutput());
    }

    public function test_validate_env_accepts_hardened_production_values(): void
    {
        $process = new Process(['./validate-env.sh'], $this->projectPath(), [
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

    public function test_backup_and_restore_fail_fast_without_database_password(): void
    {
        $backup = new Process(['./backup.sh'], $this->projectPath(), [
            'PATH' => getenv('PATH'),
            'DB_CENTRAL_PASSWORD' => '',
            'PGPASSWORD' => '',
        ]);

        $backup->run();

        $this->assertNotSame(0, $backup->getExitCode());
        $this->assertStringContainsString('DB_CENTRAL_PASSWORD ou PGPASSWORD deve estar configurado para backup', $backup->getErrorOutput());

        $dump = tempnam(sys_get_temp_dir(), 'restore-test-');
        $this->assertIsString($dump);

        $restore = new Process(['./restore.sh', $dump], $this->projectPath(), [
            'PATH' => getenv('PATH'),
            'DB_CENTRAL_PASSWORD' => '',
            'PGPASSWORD' => '',
        ]);

        $restore->run();
        @unlink($dump);

        $this->assertNotSame(0, $restore->getExitCode());
        $this->assertStringContainsString('DB_CENTRAL_PASSWORD ou PGPASSWORD deve estar configurado para restore', $restore->getErrorOutput());
    }
}
