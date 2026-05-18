<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationRollbackRules;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use App\Support\Billing\RecoveryAutomationViolationSeverity;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Tests\NonDatabaseTestCase;

class AdvancedRecoveryAutomationRollbackRulesTest extends NonDatabaseTestCase
{
    public function test_it_classifies_material_violations_and_blocks_invalid_rollbacks(): void
    {
        $rules = new AdvancedRecoveryAutomationRollbackRules;
        $candidate = new RecoveryAutomationPolicyVersion([
            'status' => RecoveryAutomationPolicyStatus::Active->value,
        ]);
        $restored = new RecoveryAutomationPolicyVersion([
            'status' => RecoveryAutomationPolicyStatus::Superseded->value,
        ]);

        $severity = $rules->classifySeverity('performance_regression', 3, 0.30);

        $this->assertSame(RecoveryAutomationViolationSeverity::Critical, $severity);
        $this->assertTrue($rules->canRollback($candidate, $restored, 1));
        $this->assertFalse($rules->canRollback($candidate, null, 1));
        $this->assertFalse($rules->canRollback($candidate, $restored, 0));
    }

    public function test_it_selects_the_latest_restorable_policy_other_than_the_current_one(): void
    {
        $rules = new AdvancedRecoveryAutomationRollbackRules;

        $first = new RecoveryAutomationPolicyVersion(['slug' => 'baseline']);
        $first->id = 10;
        $first->activation_completed_at = CarbonImmutable::now()->subDay();

        $second = new RecoveryAutomationPolicyVersion(['slug' => 'candidate']);
        $second->id = 11;
        $second->activation_completed_at = CarbonImmutable::now();

        $restored = $rules->findRestorablePolicy(new Collection([$first, $second]), 11);

        $this->assertSame(10, $restored?->id);
    }
}
