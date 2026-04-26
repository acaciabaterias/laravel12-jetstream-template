<?php

namespace Tests\Feature;

use Tests\TestCase;

class CiWorkflowReadinessTest extends TestCase
{
    public function test_test_workflow_runs_phpunit_frontend_build_and_docker_build(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/test.yml'));

        $this->assertIsString($workflow);
        $this->assertStringContainsString('php artisan test --compact', $workflow);
        $this->assertStringContainsString('actions/setup-node@v4', $workflow);
        $this->assertStringContainsString('npm ci', $workflow);
        $this->assertStringContainsString('npm run build', $workflow);
        $this->assertStringContainsString('docker compose config --quiet', $workflow);
        $this->assertStringContainsString('docker/build-push-action@v6', $workflow);
        $this->assertStringContainsString('push: false', $workflow);
    }

    public function test_lint_workflow_uses_pint_agent_format_and_php_lint(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/lint.yml'));

        $this->assertIsString($workflow);
        $this->assertStringContainsString('vendor/bin/pint --test --format agent', $workflow);
        $this->assertStringContainsString("find app bootstrap config database routes tests microservicos -name '*.php'", $workflow);
        $this->assertStringContainsString('postman_collection.json', $workflow);
    }

    public function test_deploy_workflows_validate_inputs_before_applying_changes(): void
    {
        $deployWorkflow = file_get_contents(base_path('.github/workflows/deploy.yml'));
        $kubernetesWorkflow = file_get_contents(base_path('.github/workflows/deploy-k8s.yml'));

        $this->assertIsString($deployWorkflow);
        $this->assertIsString($kubernetesWorkflow);
        $this->assertStringContainsString('docker compose config --quiet', $deployWorkflow);
        $this->assertStringContainsString('test -x backup.sh', $deployWorkflow);
        $this->assertStringContainsString('test -x restore.sh', $deployWorkflow);
        $this->assertStringContainsString('test -x healthcheck.sh', $deployWorkflow);
        $this->assertStringContainsString('kubectl kustomize infra/kubernetes/production', $kubernetesWorkflow);
        $this->assertStringContainsString('kubectl apply -k infra/kubernetes/production', $kubernetesWorkflow);
    }

    public function test_workflows_use_read_only_repository_permissions_by_default(): void
    {
        foreach (['test.yml', 'lint.yml', 'deploy.yml', 'deploy-k8s.yml'] as $workflowName) {
            $workflow = file_get_contents(base_path('.github/workflows/'.$workflowName));

            $this->assertIsString($workflow);
            $this->assertStringContainsString("permissions:\n  contents: read", $workflow);
        }
    }

    public function test_microservices_are_part_of_the_monorepo_tree(): void
    {
        $this->assertDirectoryDoesNotExist(base_path('microservicos/.git'));

        foreach ([
            'ms-001-fiscal-acbr',
            'ms-002-bancario',
            'ms-003-whatsapp-n8n',
            'ms-004-openfinance',
            'ms-005-geocoding',
        ] as $service) {
            $this->assertFileExists(base_path("microservicos/{$service}/composer.json"));
            $this->assertFileExists(base_path("microservicos/{$service}/Dockerfile"));
            $this->assertFileExists(base_path("microservicos/{$service}/routes/api.php"));
        }
    }
}
