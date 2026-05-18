<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\RecoveryAutomationExperiment;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationPolicyRules;
use App\Support\Billing\RecoveryAutomationExperimentStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Tests\TestCase;

class AdvancedRecoveryAutomationPolicyRulesTest extends TestCase
{
    public function test_it_blocks_publication_without_required_guardrails_and_fallbacks(): void
    {
        $rules = new AdvancedRecoveryAutomationPolicyRules;
        $policyVersion = new RecoveryAutomationPolicyVersion([
            'slug' => 'broken',
            'name' => 'Broken policy',
            'status' => RecoveryAutomationPolicyStatus::Draft->value,
            'guardrail_rules' => ['cooldown_hours' => 24],
            'fallback_matrix' => [],
        ]);

        $result = $rules->validatePublication($policyVersion);

        $this->assertFalse($result['passed']);
        $this->assertNotEmpty($result['messages']);
    }

    public function test_it_validates_experiment_and_assigns_forced_or_holdout_variants(): void
    {
        $rules = new AdvancedRecoveryAutomationPolicyRules;
        $experiment = new RecoveryAutomationExperiment([
            'name' => 'Q2 Holdout',
            'status' => RecoveryAutomationExperimentStatus::Draft->value,
            'control_ratio' => 0.15,
            'variant_definitions' => [
                'variant_a' => ['channel' => 'whatsapp'],
                'variant_b' => ['channel' => 'email'],
                'holdout' => ['holdout' => true],
            ],
        ]);

        $validation = $rules->validateExperiment($experiment);
        $forced = $rules->assignVariantKey(10, 0.15, [
            'forced_assignments' => ['10' => 'variant_b'],
        ], (array) $experiment->variant_definitions);
        $holdout = $rules->assignVariantKey(11, 1.0, [], (array) $experiment->variant_definitions);

        $this->assertTrue($validation['passed']);
        $this->assertSame('variant_b', $forced);
        $this->assertSame('holdout', $holdout);
    }
}
