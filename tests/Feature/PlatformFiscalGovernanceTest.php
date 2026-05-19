<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\UsuarioPlataforma;
use App\Services\Fiscal\PlatformFiscalPublicationService;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalGovernanceTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_fiscal_rules.events.publish_to_backbone', true);

        $this->runPlatformFiscalRuleMigrations(includeBackbone: true);
    }

    public function test_degraded_publication_emits_audit_event_without_promoting_the_bundle(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();

        $publication = app(PlatformFiscalPublicationService::class)->publish(
            [
                ['cfop_code' => '7101', 'description' => 'Direct export of own production', 'operation_direction' => 'export'],
                ['cfop_code' => '3101', 'description' => 'Import for resale', 'operation_direction' => 'import'],
            ],
            [
                ['scenario_key' => 'direct_export', 'cfop_code' => '7101', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_ncm']],
                ['scenario_key' => 'resale_import', 'cfop_code' => '3101', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_customs_record']],
            ],
            $billing->id,
        );

        $this->assertSame('draft', $publication->status->value);
        $this->assertSame('degraded', $publication->metadata['promotion_state']);
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'CATALOGO_FISCAL_DEGRADADO_REGISTRADO',
            'origin_context' => 'platform-fiscal-rules',
        ], 'central');
    }
}
