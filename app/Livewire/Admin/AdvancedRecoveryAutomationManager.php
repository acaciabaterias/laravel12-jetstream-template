<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Http\Requests\Admin\StoreAdvancedRecoveryAutomationExperimentRequest;
use App\Http\Requests\Admin\StoreAdvancedRecoveryAutomationPublicationRequest;
use App\Models\RecoveryAutomationPolicyVersion;
use App\Services\Billing\AdvancedRecoveryAutomationExperimentService;
use App\Services\Billing\AdvancedRecoveryAutomationInspectionService;
use App\Services\Billing\AdvancedRecoveryAutomationPublicationService;
use App\Services\Billing\AdvancedRecoveryAutomationRollbackService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class AdvancedRecoveryAutomationManager extends Component
{
    #[Url(as: 'policy')]
    public string $policyStatusFilter = '';

    #[Url(as: 'severity')]
    public string $severityFilter = '';

    public string $selectedPolicyVersionId = '';

    public string $slug = '';

    public string $name = '';

    public string $description = '';

    public string $severityScope = 'medium';

    public int $minimumOverdueDays = 1;

    public int $maximumOverdueDays = 45;

    public int $maxDispatchesPerDay = 3;

    public int $cooldownHours = 24;

    public int $suppressionHours = 48;

    public string $primaryChannel = 'whatsapp';

    public string $fallbackChannels = 'email,manual_follow_up';

    public string $experimentName = '';

    public string $controlRatio = '0.10';

    public string $variantAChannel = 'whatsapp';

    public string $variantBChannel = 'email';

    public bool $enableHoldout = true;

    public string $rollbackReason = '';

    public ?string $operationMessage = null;

    public function saveDraft(AdvancedRecoveryAutomationPublicationService $publicationService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-revenue-recovery-automation');

        $request = new StoreAdvancedRecoveryAutomationPublicationRequest;
        $validated = $this->validate($this->publicationRules(), $request->messages());

        $policyVersion = $publicationService->createDraft([
            'slug' => $validated['slug'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'scope_filters' => [
                'severity' => [$validated['severityScope']],
                'minimum_overdue_days' => $validated['minimumOverdueDays'],
                'maximum_overdue_days' => $validated['maximumOverdueDays'],
            ],
            'guardrail_rules' => [
                'max_dispatches_per_day' => $validated['maxDispatchesPerDay'],
                'cooldown_hours' => $validated['cooldownHours'],
                'suppression_hours' => $validated['suppressionHours'],
            ],
            'fallback_matrix' => [
                'stage_channels' => [
                    'd1' => array_values(array_filter([
                        trim($validated['primaryChannel']),
                        ...$this->parseCsv($validated['fallbackChannels']),
                    ])),
                ],
                'fallbacks' => $this->parseCsv($validated['fallbackChannels']),
            ],
        ], auth('platform')->id());

        $this->selectedPolicyVersionId = (string) $policyVersion->id;
        $this->operationMessage = sprintf('Politica draft %s registrada.', $policyVersion->name);
    }

    public function publishPolicy(
        AdvancedRecoveryAutomationPublicationService $publicationService,
        AdvancedRecoveryAutomationExperimentService $experimentService,
    ): void {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-revenue-recovery-automation');

        $validated = $this->validate(
            array_merge($this->publishRules(), $this->experimentRules()),
            array_merge(
                (new StoreAdvancedRecoveryAutomationPublicationRequest)->messages(),
                (new StoreAdvancedRecoveryAutomationExperimentRequest)->messages(),
            ),
        );

        $policyVersion = RecoveryAutomationPolicyVersion::query()->findOrFail((int) $validated['selectedPolicyVersionId']);
        $experiment = null;

        if ($validated['experimentName'] !== '') {
            $variantDefinitions = [
                'variant_a' => ['channel' => $validated['variantAChannel']],
                'variant_b' => ['channel' => $validated['variantBChannel']],
            ];

            if ($validated['enableHoldout']) {
                $variantDefinitions['holdout'] = ['holdout' => true];
            }

            $experiment = $experimentService->registerDraft(
                $policyVersion,
                [
                    'name' => $validated['experimentName'],
                    'control_ratio' => (float) $validated['controlRatio'],
                    'allocation_rules' => [],
                    'variant_definitions' => $variantDefinitions,
                ],
                auth('platform')->id(),
            );
        }

        $publicationService->publish($policyVersion, $experiment, auth('platform')->id());
        $this->operationMessage = sprintf('Politica %s publicada com sucesso.', $policyVersion->name);
    }

    public function rollbackPolicy(
        int $policyVersionId,
        AdvancedRecoveryAutomationRollbackService $advancedRecoveryAutomationRollbackService,
    ): void {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-revenue-recovery-automation');

        $validated = $this->validate([
            'rollbackReason' => ['required', 'string', 'max:255'],
        ]);

        $policyVersion = RecoveryAutomationPolicyVersion::query()->findOrFail($policyVersionId);
        $advancedRecoveryAutomationRollbackService->rollback(
            $policyVersion,
            $validated['rollbackReason'],
            auth('platform')->id(),
        );

        $this->operationMessage = sprintf('Rollback da politica %s registrado.', $policyVersion->name);
    }

    public function render(AdvancedRecoveryAutomationInspectionService $advancedRecoveryAutomationInspectionService): View
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-advanced-revenue-recovery-automation');

        $inspection = $advancedRecoveryAutomationInspectionService->inspect([
            'policy_status' => $this->policyStatusFilter,
            'severity' => $this->severityFilter,
            'limit' => 25,
        ]);

        return view('livewire.admin.advanced-recovery-automation-manager', [
            'summary' => $inspection['summary'],
            'policies' => $inspection['policies'],
            'violations' => $inspection['violations'],
            'journeys' => $inspection['journeys'],
            'rollbackContexts' => $inspection['rollback_contexts'],
        ]);
    }

    public function publicationRules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:120'],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'severityScope' => ['required', 'string'],
            'minimumOverdueDays' => ['required', 'integer', 'min:0'],
            'maximumOverdueDays' => ['required', 'integer', 'min:1'],
            'maxDispatchesPerDay' => ['required', 'integer', 'min:1'],
            'cooldownHours' => ['required', 'integer', 'min:1'],
            'suppressionHours' => ['required', 'integer', 'min:1'],
            'primaryChannel' => ['required', 'string', 'max:60'],
            'fallbackChannels' => ['required', 'string'],
        ];
    }

    public function publishRules(): array
    {
        return [
            'selectedPolicyVersionId' => ['required', 'integer', 'min:1'],
        ];
    }

    public function experimentRules(): array
    {
        return [
            'experimentName' => ['nullable', 'string', 'max:120'],
            'controlRatio' => ['required', 'numeric', 'min:0', 'max:1'],
            'variantAChannel' => ['required', 'string', 'max:60'],
            'variantBChannel' => ['required', 'string', 'max:60'],
            'enableHoldout' => ['boolean'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function parseCsv(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value))));
    }
}
