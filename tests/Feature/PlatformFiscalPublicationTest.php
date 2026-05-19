<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\PlatformFiscalRuleManager;
use App\Models\UsuarioPlataforma;
use Livewire\Livewire;
use Tests\Concerns\InteractsWithPlatformFiscalRuleSetup;
use Tests\TestCase;

class PlatformFiscalPublicationTest extends TestCase
{
    use InteractsWithPlatformFiscalRuleSetup;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('platform_fiscal_rules.events.publish_to_backbone', true);

        $this->runPlatformFiscalRuleMigrations(includeBackbone: true);
    }

    public function test_billing_operator_can_publish_a_healthy_fiscal_bundle_and_emit_backbone_event(): void
    {
        $billing = UsuarioPlataforma::factory()->billing()->create();
        $this->actingAs($billing, 'platform');

        Livewire::test(PlatformFiscalRuleManager::class)
            ->set('catalogEntries', [
                ['cfop_code' => '7101', 'description' => 'Direct export of own production', 'operation_direction' => 'export'],
                ['cfop_code' => '7501', 'description' => 'Indirect export remittance', 'operation_direction' => 'export'],
                ['cfop_code' => '3101', 'description' => 'Import for resale', 'operation_direction' => 'import'],
                ['cfop_code' => '3551', 'description' => 'Import for industrialization', 'operation_direction' => 'import'],
            ])
            ->set('scenarioMappings', [
                ['scenario_key' => 'direct_export', 'cfop_code' => '7101', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_ncm']],
                ['scenario_key' => 'indirect_export', 'cfop_code' => '7501', 'classification_code' => '85072010', 'operation_direction' => 'export', 'validation_flags' => ['requires_export_commitment']],
                ['scenario_key' => 'resale_import', 'cfop_code' => '3101', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_customs_record']],
                ['scenario_key' => 'industrial_import', 'cfop_code' => '3551', 'classification_code' => '85072010', 'operation_direction' => 'import', 'validation_flags' => ['requires_ncm']],
            ])
            ->call('publishRules')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('fiscal_rule_publication_records', [
            'status' => 'active',
            'published_by' => $billing->id,
        ], 'central');
        $this->assertDatabaseHas('fiscal_rule_mappings', [
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'CATALOGO_FISCAL_PUBLICADO',
            'origin_context' => 'platform-fiscal-rules',
        ], 'central');
    }
}
