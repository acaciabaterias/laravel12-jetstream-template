<?php

namespace Tests\Feature;

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

    public function test_super_admin_seeder_rejects_placeholder_passwords_in_production(): void
    {
        $seeder = file_get_contents(base_path('database/seeders/SuperAdminSeeder.php'));

        $this->assertIsString($seeder);
        $this->assertStringContainsString("app()->environment('production')", $seeder);
        $this->assertStringContainsString('change-me-before-deploy', $seeder);
        $this->assertStringContainsString('Configure SUPER_ADMIN_PASSWORD', $seeder);
    }
}
