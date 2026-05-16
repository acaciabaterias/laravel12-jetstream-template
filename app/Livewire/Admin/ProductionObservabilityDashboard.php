<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\OperationalIncidentRecord;
use App\Services\Operations\LoadTestBaselineService;
use App\Services\Operations\OperationalHealthSnapshotService;
use App\Services\Operations\OperationalIncidentService;
use App\Services\Operations\ProductionObservabilitySummaryService;
use App\Services\Operations\RunbookEvidenceService;
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

    #[Url(as: 'incidente')]
    public string $incidentStatusFilter = '';

    public string $scenarioName = '';

    public string $baselineFlowName = '';

    public string $throughputPerMinute = '';

    public string $p95LatencyMs = '';

    public string $errorRate = '';

    public string $environmentNotes = '';

    public string $selectedIncidentId = '';

    public string $incidentExecutionType = 'replay';

    public string $incidentResultStatus = 'success';

    public string $incidentNotes = '';

    public string $incidentValidationChecks = '';

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

    public function updatingIncidentStatusFilter(): void
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
            'scenario_name' => $validated['scenarioName'],
            'flow_name' => $validated['baselineFlowName'],
            'throughput_per_minute' => (int) $validated['throughputPerMinute'],
            'p95_latency_ms' => (int) $validated['p95LatencyMs'],
            'error_rate' => (float) $validated['errorRate'],
            'environment_notes' => $validated['environmentNotes'],
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
            'scenario_name' => $validated['scenarioName'],
            'flow_name' => $validated['baselineFlowName'],
            'throughput_per_minute' => (int) $validated['throughputPerMinute'],
            'p95_latency_ms' => (int) $validated['p95LatencyMs'],
            'error_rate' => (float) $validated['errorRate'],
        ]);

        $this->operationMessage = match ($this->comparisonResult['status']) {
            'missing_baseline' => 'Nenhum baseline aceito foi encontrado para este fluxo e cenario.',
            'within_tolerance' => 'Execucao dentro da faixa aceitavel do baseline.',
            default => 'Execucao com regressao operacional acima da tolerancia.',
        };
    }

    public function acknowledgeIncident(int $incidentId, OperationalIncidentService $operationalIncidentService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $operationalIncidentService->acknowledge($this->findIncident($incidentId));
        $this->operationMessage = sprintf('Incidente %d reconhecido com sucesso.', $incidentId);
    }

    public function resolveIncident(int $incidentId, OperationalIncidentService $operationalIncidentService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $operationalIncidentService->resolve($this->findIncident($incidentId), [
            'resolved_by' => auth('platform')->id(),
            'resolution_notes' => $this->incidentNotes,
        ]);

        $this->operationMessage = sprintf('Incidente %d marcado como resolvido.', $incidentId);
    }

    public function recordRunbookEvidence(RunbookEvidenceService $runbookEvidenceService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $validated = $this->validate($this->incidentActionRules());
        $incident = $this->findIncident((int) $validated['selectedIncidentId']);

        $runbookEvidenceService->record($incident, [
            'execution_type' => $validated['incidentExecutionType'],
            'operator_user_id' => auth('platform')->id(),
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
            'result_status' => $validated['incidentResultStatus'],
            'evidence_payload' => [
                'validation_checks' => $this->parsedValidationChecks(),
            ],
            'notes' => $validated['incidentNotes'],
            'metadata' => ['captured_from' => 'production_observability_dashboard'],
        ]);

        $this->operationMessage = sprintf('Evidencia operacional registrada no incidente %d.', $incident->id);
    }

    public function closeIncident(OperationalIncidentService $operationalIncidentService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $validated = $this->validate($this->incidentActionRules());
        $incident = $this->findIncident((int) $validated['selectedIncidentId']);

        $operationalIncidentService->close($incident, [
            'post_validation_passed' => true,
            'validated_checks' => $this->parsedValidationChecks(),
            'validated_by' => auth('platform')->id(),
            'notes' => $validated['incidentNotes'],
        ]);

        $this->operationMessage = sprintf('Incidente %d encerrado com validacao registrada.', $incident->id);
    }

    /**
     * @return array<string, mixed>
     */
    protected function baselineRules(): array
    {
        return [
            'scenarioName' => ['required', 'string', 'max:120'],
            'baselineFlowName' => ['required', 'string', Rule::in($this->availableFlows())],
            'throughputPerMinute' => ['required', 'integer', 'min:1'],
            'p95LatencyMs' => ['required', 'integer', 'min:1'],
            'errorRate' => ['required', 'numeric', 'min:0', 'max:1'],
            'environmentNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function incidentActionRules(): array
    {
        return [
            'selectedIncidentId' => ['required', 'integer', 'min:1'],
            'incidentExecutionType' => ['required', 'string', Rule::in(['replay', 'rollback', 'restore_validation', 'contingency'])],
            'incidentResultStatus' => ['required', 'string', Rule::in(['success', 'partial', 'failed'])],
            'incidentNotes' => ['nullable', 'string', 'max:1000'],
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

    /**
     * @return array<int, string>
     */
    protected function parsedValidationChecks(): array
    {
        return collect(explode(',', $this->incidentValidationChecks))
            ->map(fn (string $check): string => trim($check))
            ->filter()
            ->values()
            ->all();
    }

    protected function findIncident(int $incidentId): OperationalIncidentRecord
    {
        return OperationalIncidentRecord::query()->findOrFail($incidentId);
    }

    public function render(ProductionObservabilitySummaryService $productionObservabilitySummaryService)
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-production-observability');

        $latestSnapshots = $productionObservabilitySummaryService->latestOrRebuild();
        $recentIncidents = OperationalIncidentRecord::query()
            ->with(['evidences.operator'])
            ->when($this->flowNameFilter !== '', fn ($query) => $query->where('flow_name', $this->flowNameFilter))
            ->when($this->incidentStatusFilter !== '', fn ($query) => $query->where('status', $this->incidentStatusFilter))
            ->latest('opened_at')
            ->limit(8)
            ->get();

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
            'recentIncidents' => $recentIncidents,
            'operationMessage' => $this->operationMessage,
        ]);
    }
}
