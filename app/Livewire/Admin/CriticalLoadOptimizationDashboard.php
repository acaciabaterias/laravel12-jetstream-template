<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\BenchmarkExecutionRecord;
use App\Models\LoadScenarioProfile;
use App\Models\TuningChangeRecord;
use App\Services\Operations\CriticalLoadBenchmarkService;
use App\Services\Operations\CriticalLoadBottleneckAnalysisService;
use App\Services\Operations\CriticalLoadInspectionService;
use App\Services\Operations\CriticalLoadRollbackEvidenceService;
use App\Services\Operations\CriticalLoadTuningLifecycleService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class CriticalLoadOptimizationDashboard extends Component
{
    #[Url(as: 'fluxo')]
    public string $flowNameFilter = '';

    #[Url(as: 'comparacao')]
    public string $comparisonStatusFilter = '';

    #[Url(as: 'categoria')]
    public string $categoryFilter = '';

    #[Url(as: 'ambiente')]
    public string $environmentFilter = '';

    public string $scenarioName = '';

    public string $scenarioFlowName = 'integration_backbone';

    public string $scenarioEnvironment = 'staging';

    public int $requestBudget = 300;

    public int $durationSeconds = 120;

    public int $concurrencyLevel = 10;

    public int $expectedThroughputPerMinute = 250;

    public int $expectedP95LatencyMs = 900;

    public float $expectedErrorRate = 0.01;

    public int $selectedScenarioId = 0;

    public int $throughputPerMinute = 0;

    public int $p95LatencyMs = 0;

    public float $errorRate = 0.0;

    public int $selectedExecutionId = 0;

    public string $bottleneckFlowName = 'integration_backbone';

    public string $bottleneckCategory = 'database';

    public string $componentName = '';

    public string $bottleneckSummary = '';

    public string $impactLevel = 'warning';

    public string $changeKey = '';

    public string $hypothesisSummary = '';

    public string $changeType = 'index';

    public int $selectedValidationExecutionId = 0;

    public ?string $operationMessage = null;

    public function registerScenario(CriticalLoadBenchmarkService $criticalLoadBenchmarkService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $validated = $this->validate([
            'scenarioName' => ['required', 'string', 'max:120'],
            'scenarioFlowName' => ['required', 'string', 'max:80'],
            'scenarioEnvironment' => ['required', 'string', 'max:40'],
            'requestBudget' => ['required', 'integer', 'min:1'],
            'durationSeconds' => ['required', 'integer', 'min:1'],
            'concurrencyLevel' => ['required', 'integer', 'min:1'],
            'expectedThroughputPerMinute' => ['required', 'integer', 'min:1'],
            'expectedP95LatencyMs' => ['required', 'integer', 'min:1'],
            'expectedErrorRate' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        $scenario = $criticalLoadBenchmarkService->registerScenario([
            'flow_name' => $validated['scenarioFlowName'],
            'scenario_name' => $validated['scenarioName'],
            'environment' => $validated['scenarioEnvironment'],
            'request_budget' => $validated['requestBudget'],
            'duration_seconds' => $validated['durationSeconds'],
            'concurrency_level' => $validated['concurrencyLevel'],
            'expected_throughput_per_minute' => $validated['expectedThroughputPerMinute'],
            'expected_p95_latency_ms' => $validated['expectedP95LatencyMs'],
            'expected_error_rate' => (float) $validated['expectedErrorRate'],
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->selectedScenarioId = $scenario->id;
        $this->operationMessage = sprintf('Cenario %s registrado com sucesso.', $scenario->scenario_name);
    }

    public function recordExecution(CriticalLoadBenchmarkService $criticalLoadBenchmarkService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $validated = $this->validate([
            'selectedScenarioId' => ['required', 'integer', 'min:1'],
            'throughputPerMinute' => ['required', 'integer', 'min:1'],
            'p95LatencyMs' => ['required', 'integer', 'min:1'],
            'errorRate' => ['required', 'numeric', 'min:0', 'max:1'],
        ]);

        $scenario = LoadScenarioProfile::query()->findOrFail($validated['selectedScenarioId']);
        $execution = $criticalLoadBenchmarkService->recordExecution($scenario, [
            'throughput_per_minute' => $validated['throughputPerMinute'],
            'p95_latency_ms' => $validated['p95LatencyMs'],
            'error_rate' => (float) $validated['errorRate'],
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Benchmark %d registrado com status %s.', $execution->id, $execution->comparison_status->value);
    }

    public function promoteBaseline(int $executionId, CriticalLoadBenchmarkService $criticalLoadBenchmarkService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $execution = BenchmarkExecutionRecord::query()->with('scenario')->findOrFail($executionId);
        $criticalLoadBenchmarkService->promoteBaseline($execution->scenario, $execution);

        $this->operationMessage = sprintf('Baseline do cenario %s promovida.', $execution->scenario->scenario_name);
    }

    public function recordBottleneck(CriticalLoadBottleneckAnalysisService $criticalLoadBottleneckAnalysisService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $validated = $this->validate([
            'selectedExecutionId' => ['required', 'integer', 'min:1'],
            'bottleneckFlowName' => ['required', 'string', 'max:80'],
            'bottleneckCategory' => ['required', 'string', 'max:30'],
            'componentName' => ['required', 'string', 'max:120'],
            'bottleneckSummary' => ['required', 'string', 'max:1000'],
            'impactLevel' => ['required', 'string', 'max:20'],
        ]);

        $execution = BenchmarkExecutionRecord::query()->findOrFail($validated['selectedExecutionId']);
        $bottleneck = $criticalLoadBottleneckAnalysisService->record($execution, [
            'flow_name' => $validated['bottleneckFlowName'],
            'category' => $validated['bottleneckCategory'],
            'component_name' => $validated['componentName'],
            'summary' => $validated['bottleneckSummary'],
            'impact_level' => $validated['impactLevel'],
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Gargalo %s registrado para %s.', $bottleneck->category->value, $bottleneck->component_name);
    }

    public function registerTuningChange(CriticalLoadTuningLifecycleService $criticalLoadTuningLifecycleService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $validated = $this->validate([
            'selectedExecutionId' => ['required', 'integer', 'min:1'],
            'bottleneckFlowName' => ['required', 'string', 'max:80'],
            'scenarioEnvironment' => ['required', 'string', 'max:40'],
            'changeKey' => ['required', 'string', 'max:120'],
            'hypothesisSummary' => ['required', 'string', 'max:1000'],
            'changeType' => ['required', 'string', 'max:60'],
        ]);

        $change = $criticalLoadTuningLifecycleService->register([
            'flow_name' => $validated['bottleneckFlowName'],
            'environment' => $validated['scenarioEnvironment'],
            'change_key' => $validated['changeKey'],
            'hypothesis_summary' => $validated['hypothesisSummary'],
            'change_type' => $validated['changeType'],
            'baseline_execution_id' => $validated['selectedExecutionId'],
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Tuning %s registrado com sucesso.', $change->change_key);
    }

    public function validateTuning(int $changeId, CriticalLoadTuningLifecycleService $criticalLoadTuningLifecycleService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $validated = $this->validate([
            'selectedValidationExecutionId' => ['required', 'integer', 'min:1'],
        ]);

        $change = TuningChangeRecord::query()->findOrFail($changeId);
        $execution = BenchmarkExecutionRecord::query()->findOrFail($validated['selectedValidationExecutionId']);
        $criticalLoadTuningLifecycleService->validate($change, $execution);

        $this->operationMessage = sprintf('Tuning %s validado com benchmark %d.', $change->change_key, $execution->id);
    }

    public function promoteTuning(int $changeId, CriticalLoadTuningLifecycleService $criticalLoadTuningLifecycleService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $change = TuningChangeRecord::query()->findOrFail($changeId);
        $criticalLoadTuningLifecycleService->promote($change);

        $this->operationMessage = sprintf('Tuning %s promovido.', $change->change_key);
    }

    public function rollbackTuning(
        int $changeId,
        CriticalLoadTuningLifecycleService $criticalLoadTuningLifecycleService,
        CriticalLoadRollbackEvidenceService $criticalLoadRollbackEvidenceService,
    ): void {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $change = TuningChangeRecord::query()->findOrFail($changeId);
        $rolledBack = $criticalLoadTuningLifecycleService->rollback($change);
        $criticalLoadRollbackEvidenceService->record($rolledBack, [
            'operator_user_id' => auth('platform')->id(),
            'result_status' => 'success',
            'rollback_reason' => 'Rollback operacional registrado pelo dashboard.',
            'metadata' => ['source' => 'dashboard'],
        ]);

        $this->operationMessage = sprintf('Rollback do tuning %s registrado.', $change->change_key);
    }

    public function render(CriticalLoadInspectionService $criticalLoadInspectionService): View
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-critical-load-optimization');

        $inspection = $criticalLoadInspectionService->inspect([
            'flow_name' => $this->flowNameFilter,
            'comparison_status' => $this->comparisonStatusFilter,
            'category' => $this->categoryFilter,
            'environment' => $this->environmentFilter,
        ]);

        return view('livewire.admin.critical-load-optimization-dashboard', [
            'summary' => $inspection['summary'],
            'scenarios' => $inspection['scenarios'],
            'executions' => $inspection['executions'],
            'bottlenecks' => $inspection['bottlenecks'],
            'tuningChanges' => $inspection['tuning_changes'],
            'rollbackEvidences' => $inspection['rollback_evidences'],
            'operationMessage' => $this->operationMessage,
        ]);
    }
}
