<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Operations\LoadTestBaselineService;
use App\Services\Operations\OperationalHealthSnapshotService;
use App\Services\Operations\ProductionObservabilitySummaryService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ProductionObservabilityDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'fluxo')]
    public string $flowNameFilter = '';

    #[Url(as: 'severidade')]
    public string $severityFilter = '';

    #[Url(as: 'status')]
    public string $statusFilter = '';

    public string $scenarioName = '';

    public string $baselineFlowName = '';

    public string $throughputPerMinute = '';

    public string $p95LatencyMs = '';

    public string $errorRate = '';

    public string $environmentNotes = '';

    public ?string $operationMessage = null;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $comparisonResult = null;

    public function updatingFlowNameFilter(): void
    {
        if ($this->baselineFlowName === '') {
            $this->baselineFlowName = $this->flowNameFilter;
        }

        $this->resetPage();
    }

    public function updatingSeverityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function rebuild(OperationalHealthSnapshotService $operationalHealthSnapshotService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $operationalHealthSnapshotService->rebuild();
        $this->operationMessage = 'Snapshot operacional reconstruido com sucesso.';
    }

    public function saveBaseline(LoadTestBaselineService $loadTestBaselineService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $validated = $this->validate($this->baselineRules());

        $loadTestBaselineService->record([
            'scenario_name' => $validated['scenario_name'],
            'flow_name' => $validated['baseline_flow_name'],
            'throughput_per_minute' => (int) $validated['throughput_per_minute'],
            'p95_latency_ms' => (int) $validated['p95_latency_ms'],
            'error_rate' => (float) $validated['error_rate'],
            'environment_notes' => $validated['environment_notes'],
            'accepted_at' => now(),
            'metadata' => ['captured_from' => 'production_observability_dashboard'],
        ]);

        $this->comparisonResult = null;
        $this->operationMessage = 'Baseline de carga registrado com sucesso.';
    }

    public function compareBaseline(LoadTestBaselineService $loadTestBaselineService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $validated = $this->validate($this->baselineRules());

        $this->comparisonResult = $loadTestBaselineService->compare([
            'scenario_name' => $validated['scenario_name'],
            'flow_name' => $validated['baseline_flow_name'],
            'throughput_per_minute' => (int) $validated['throughput_per_minute'],
            'p95_latency_ms' => (int) $validated['p95_latency_ms'],
            'error_rate' => (float) $validated['error_rate'],
        ]);

        $this->operationMessage = match ($this->comparisonResult['status']) {
            'missing_baseline' => 'Nenhum baseline aceito foi encontrado para este fluxo e cenario.',
            'within_tolerance' => 'Execucao dentro da faixa aceitavel do baseline.',
            default => 'Execucao com regressao operacional acima da tolerancia.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function baselineRules(): array
    {
        return [
            'scenario_name' => ['required', 'string', 'max:120'],
            'baseline_flow_name' => ['required', 'string', Rule::in($this->availableFlows())],
            'throughput_per_minute' => ['required', 'integer', 'min:1'],
            'p95_latency_ms' => ['required', 'integer', 'min:1'],
            'error_rate' => ['required', 'numeric', 'min:0', 'max:1'],
            'environment_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function availableFlows(): array
    {
        return [
            'integration_backbone',
            'platform_payments',
            'platform_recovery',
            'platform_analytics',
        ];
    }

    public function render(ProductionObservabilitySummaryService $productionObservabilitySummaryService)
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $latestSnapshots = $productionObservabilitySummaryService->latestOrRebuild();

        return view('livewire.admin.production-observability-dashboard', [
            'summary' => $productionObservabilitySummaryService->summarize(),
            'latestSnapshots' => $latestSnapshots,
            'snapshots' => $productionObservabilitySummaryService->snapshots([
                'flow_name' => $this->flowNameFilter,
                'severity' => $this->severityFilter,
                'status' => $this->statusFilter,
            ]),
            'availableFlows' => $this->availableFlows(),
            'comparisonResult' => $this->comparisonResult,
            'recentBaselines' => app(LoadTestBaselineService::class)->latest($this->flowNameFilter !== '' ? $this->flowNameFilter : null),
            'operationMessage' => $this->operationMessage,
        ]);
    }
}
