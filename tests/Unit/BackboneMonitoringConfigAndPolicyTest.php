<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\UsuarioPlataforma;
use App\Policies\BackboneMonitoringPolicy;
use Tests\NonDatabaseTestCase;

class BackboneMonitoringConfigAndPolicyTest extends NonDatabaseTestCase
{
    public function test_monitoring_consolidation_config_exposes_expected_defaults(): void
    {
        $this->assertSame(1500, config('monitoring_consolidation.scrape.latency_warning_ms'));
        $this->assertSame(5000, config('monitoring_consolidation.scrape.latency_critical_ms'));
        $this->assertSame(1, config('monitoring_consolidation.scrape.minimum_sample_count'));
        $this->assertSame('latency_ms', config('monitoring_consolidation.alerts.default_metric'));
        $this->assertSame('gte', config('monitoring_consolidation.alerts.default_operator'));
        $this->assertSame('MONITORAMENTO_DEGRADADO', config('monitoring_consolidation.alerts.material_event_type'));
        $this->assertSame('staging', config('monitoring_consolidation.provisioning.default_environment'));
        $this->assertTrue(config('monitoring_consolidation.events.publish_to_backbone'));
    }

    public function test_backbone_monitoring_policy_matches_platform_operations_roles(): void
    {
        $policy = app(BackboneMonitoringPolicy::class);
        $support = UsuarioPlataforma::factory()->make(['papel' => 'support', 'ativo' => true]);
        $inactive = UsuarioPlataforma::factory()->make(['papel' => 'support', 'ativo' => false]);

        $this->assertTrue($policy->viewAny($support));
        $this->assertFalse($policy->viewAny($inactive));
        $this->assertFalse($policy->delete($support));
    }
}
