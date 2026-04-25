<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TerraformInfrastructureFilesTest extends TestCase
{
    public function test_terraform_directories_exist_for_all_requested_providers(): void
    {
        $directories = [
            base_path('infra/terraform/digitalocean'),
            base_path('infra/terraform/aws'),
            base_path('infra/terraform/proxmox'),
        ];

        foreach ($directories as $directory) {
            $this->assertDirectoryExists($directory);
        }
    }

    public function test_each_provider_has_versions_variables_outputs_and_env_example(): void
    {
        $providers = ['digitalocean', 'aws', 'proxmox'];
        $requiredFiles = [
            'versions.tf',
            'variables.tf',
            'main.tf',
            'outputs.tf',
            'terraform.tfvars.example',
            '.env.example',
        ];

        foreach ($providers as $provider) {
            foreach ($requiredFiles as $file) {
                $this->assertFileExists(base_path("infra/terraform/{$provider}/{$file}"));
            }
        }
    }

    public function test_requested_resources_are_present_in_terraform_files(): void
    {
        $digitalOcean = file_get_contents(base_path('infra/terraform/digitalocean/main.tf'));
        $aws = file_get_contents(base_path('infra/terraform/aws/main.tf'));
        $proxmox = file_get_contents(base_path('infra/terraform/proxmox/main.tf'));

        $this->assertIsString($digitalOcean);
        $this->assertIsString($aws);
        $this->assertIsString($proxmox);

        $this->assertStringContainsString('digitalocean_droplet', $digitalOcean);
        $this->assertStringContainsString('digitalocean_volume', $digitalOcean);
        $this->assertStringContainsString('digitalocean_firewall', $digitalOcean);

        $this->assertStringContainsString('aws_instance', $aws);
        $this->assertStringContainsString('aws_db_instance', $aws);
        $this->assertStringContainsString('aws_elasticache_cluster', $aws);

        $this->assertStringContainsString('proxmox_vm_qemu', $proxmox);
        $this->assertStringContainsString('proxmox_lxc', $proxmox);
    }
}
