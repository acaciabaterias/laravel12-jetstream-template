<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CasoRecuperacaoReceita;
use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationDispatchRules;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Tests\TestCase;

class AdvancedRecoveryAutomationDispatchRulesTest extends TestCase
{
    public function test_it_generates_the_same_dispatch_key_for_the_same_stage_and_channel(): void
    {
        $rules = new AdvancedRecoveryAutomationDispatchRules;
        $case = new CasoRecuperacaoReceita([
            'id' => 10,
            'fatura_saas_id' => 20,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'severity' => RevenueRecoverySeverity::Medium->value,
        ]);
        $case->exists = true;

        $journey = new RecoveryAutomationJourney([
            'id' => 99,
            'caso_recuperacao_receita_id' => $case->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);
        $journey->setRelation('recoveryCase', $case);
        $journey->exists = true;

        $first = $rules->makeDispatchKey($journey, 'd1', 'email');
        $second = $rules->makeDispatchKey($journey, 'd1', 'email');
        $different = $rules->makeDispatchKey($journey, 'd3', 'whatsapp');

        $this->assertSame($first, $second);
        $this->assertNotSame($first, $different);
    }

    public function test_it_detects_active_cooldown_and_resolves_unique_fallback_channels(): void
    {
        $rules = new AdvancedRecoveryAutomationDispatchRules;
        $policyVersion = new RecoveryAutomationPolicyVersion([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
            'guardrail_rules' => [
                'cooldown_hours' => 24,
            ],
            'fallback_matrix' => [
                'stage_channels' => [
                    'd1' => ['whatsapp', 'email', 'email', 'manual_follow_up'],
                ],
            ],
        ]);
        $journey = new RecoveryAutomationJourney([
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
            'last_dispatched_at' => now()->subHours(2),
        ]);

        $this->assertTrue($rules->isWithinCooldown($journey, $policyVersion));
        $this->assertSame(
            ['whatsapp', 'email', 'manual_follow_up'],
            $rules->resolveChannels([
                'name' => 'd1',
                'channel' => 'whatsapp',
                'delay_hours' => 0,
            ], $policyVersion),
        );
    }
}
