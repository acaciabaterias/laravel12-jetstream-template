<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\RecoveryAutomationJourney;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Models\RecoveryAutomationViolation;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\RecoveryAutomationJourneyStatus;
use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AdvancedRecoveryAutomationInspectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'database/migrations/central/2026_04_23_000001_create_central_catalog_tables.php',
            'database/migrations/central/2026_04_23_000002_create_central_billing_tables.php',
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

    public function test_inspection_endpoint_returns_policy_performance_and_violation_evidence(): void
    {
        $operator = UsuarioPlataforma::factory()->billing()->create();
        $activePolicy = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'adaptive-live',
            'status' => RecoveryAutomationPolicyStatus::Active->value,
        ]);
        $rolledBackPolicy = RecoveryAutomationPolicyVersion::factory()->create([
            'slug' => 'adaptive-rollback',
            'status' => RecoveryAutomationPolicyStatus::RolledBack->value,
            'metadata' => [
                'rollback' => [
                    'restored_policy_version_id' => $activePolicy->id,
                    'affected_journeys' => 2,
                ],
            ],
        ]);
        $journey = RecoveryAutomationJourney::factory()->create([
            'recovery_automation_policy_version_id' => $activePolicy->id,
            'journey_status' => RecoveryAutomationJourneyStatus::Active->value,
        ]);
        RecoveryAutomationViolation::factory()->create([
            'recovery_automation_policy_version_id' => $activePolicy->id,
            'recovery_automation_journey_id' => $journey->id,
            'violation_type' => 'performance_regression',
            'severity' => 'critical',
            'resolution_status' => 'open',
            'summary' => 'Conversao abaixo da referencia e backlog crescente.',
            'evidence_payload' => ['conversion_delta' => -0.31],
        ]);
        RecoveryAutomationViolation::factory()->create([
            'recovery_automation_policy_version_id' => $rolledBackPolicy->id,
            'violation_type' => 'duplicate_dispatch',
            'severity' => 'high',
            'resolution_status' => 'rolled_back',
        ]);

        $response = $this
            ->actingAs($operator, 'platform')
            ->getJson(route('admin.recovery.automation.inspection', [
                'policy_status' => 'active',
                'severity' => 'critical',
            ]));

        $response
            ->assertOk()
            ->assertJsonPath('summary.active_policies', 1)
            ->assertJsonPath('summary.critical_violations', 1)
            ->assertJsonPath('policies.0.slug', 'adaptive-live')
            ->assertJsonPath('violations.0.violation_type', 'performance_regression')
            ->assertJsonPath('violations.0.evidence_payload.conversion_delta', -0.31);
    }
}
