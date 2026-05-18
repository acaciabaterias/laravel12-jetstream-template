<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\Admin\AdvancedRecoveryAutomationManager;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\RecoveryAutomationExperimentStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Support\Facades\Artisan;
use Livewire\Livewire;
use Tests\TestCase;

class AdvancedRecoveryAutomationPublicationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('advanced_revenue_recovery_automation.events.publish_to_backbone', true);

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
            'database/migrations/central/2026_05_08_123000_create_central_integration_backbone_tables.php',
            'database/migrations/central/2026_05_07_205216_alter_platform_billing_tables_for_module_011.php',
            'database/migrations/central/2026_05_08_131046_create_central_platform_payments_tables.php',
            'database/migrations/central/2026_05_08_190000_create_central_platform_revenue_recovery_tables.php',
            'database/migrations/central/2026_05_16_130000_create_central_advanced_revenue_recovery_automation_tables.php',
        ] as $migrationPath) {
            Artisan::call('migrate', [
                '--database' => 'central',
                '--path' => $migrationPath,
                '--force' => true,
            ]);
        }
    }

    public function test_billing_operator_can_publish_a_controlled_policy_from_livewire_manager(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $previousActive = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'legacy-policy',
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'fallback_matrix' => [
                'stage_channels' => ['d1' => ['email', 'manual_follow_up']],
                'fallbacks' => ['manual_follow_up'],
            ],
            'guardrail_rules' => [
                'max_dispatches_per_day' => 2,
                'cooldown_hours' => 12,
                'suppression_hours' => 24,
            ],
        ]);
        $draft = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'adaptive-2026-q2',
            'name' => 'Adaptive 2026 Q2',
            'status' => RecoveryAutomationPolicyStatus::Draft->value,
            'fallback_matrix' => [
                'stage_channels' => ['d1' => ['whatsapp', 'email', 'manual_follow_up']],
                'fallbacks' => ['email', 'manual_follow_up'],
            ],
            'guardrail_rules' => [
                'max_dispatches_per_day' => 3,
                'cooldown_hours' => 24,
                'suppression_hours' => 48,
            ],
        ]);

        $this->actingAs($operator, 'platform');

        Livewire::test(AdvancedRecoveryAutomationManager::class)
            ->set('selectedPolicyVersionId', (string) $draft->id)
            ->set('experimentName', 'Q2 Holdout')
            ->set('controlRatio', '0.20')
            ->set('variantAChannel', 'whatsapp')
            ->set('variantBChannel', 'email')
            ->set('enableHoldout', true)
            ->call('publishPolicy')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('recovery_automation_policy_versions', [
            'id' => $previousActive->id,
            'status' => RecoveryAutomationPolicyStatus::Superseded->value,
            'superseded_by_policy_version_id' => $draft->id,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_policy_versions', [
            'id' => $draft->id,
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'approved_by' => $operator->id,
        ], 'central');
        $this->assertDatabaseHas('recovery_automation_experiments', [
            'recovery_automation_policy_version_id' => $draft->id,
            'name' => 'Q2 Holdout',
            'status' => RecoveryAutomationExperimentStatus::Running->value,
        ], 'central');
        $this->assertDatabaseHas('evento_outboxes', [
            'event_type' => 'POLITICA_AUTOMACAO_RECUPERACAO_PUBLICADA',
            'origin_context' => 'advanced-revenue-recovery-automation',
        ], 'central');
    }
}
