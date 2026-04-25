<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MonitoringConfigurationTest extends TestCase
{
    public function test_monitoring_files_exist(): void
    {
        $paths = [
            base_path('docker/monitoring/prometheus.yml'),
            base_path('docker/monitoring/alert-rules.yml'),
            base_path('docker/monitoring/grafana-dashboards/erp-core.json'),
            base_path('docker/monitoring/grafana-dashboards/microservices.json'),
            base_path('docker-compose.monitoring.yml'),
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path);
        }
    }

    public function test_monitoring_compose_references_prometheus_grafana_and_exporters(): void
    {
        $compose = file_get_contents(base_path('docker-compose.monitoring.yml'));

        $this->assertIsString($compose);
        $this->assertStringContainsString('prometheus:', $compose);
        $this->assertStringContainsString('grafana:', $compose);
        $this->assertStringContainsString('blackbox-exporter:', $compose);
        $this->assertStringContainsString('cadvisor:', $compose);
        $this->assertStringContainsString('docker/monitoring/prometheus.yml', $compose);
        $this->assertStringContainsString('docker/monitoring/grafana-dashboards', $compose);
    }

    public function test_prometheus_configuration_scrapes_erp_and_microservices(): void
    {
        $prometheus = file_get_contents(base_path('docker/monitoring/prometheus.yml'));

        $this->assertIsString($prometheus);
        $this->assertStringContainsString('job_name: erp_http_health', $prometheus);
        $this->assertStringContainsString('job_name: microservices_http_health', $prometheus);
        $this->assertStringContainsString('http://erp_core_app/up', $prometheus);
        $this->assertStringContainsString('http://ms_001_fiscal_api:8001/api/v1/health', $prometheus);
        $this->assertStringContainsString('http://ms_005_geocoding_api:8005/api/v1/health', $prometheus);
    }
}
