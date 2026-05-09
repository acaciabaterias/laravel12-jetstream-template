<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Operations\OperationalHealthSnapshotService;
use App\Services\Operations\ProductionObservabilitySummaryService;
use Illuminate\Support\Facades\Gate;
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

    public ?string $operationMessage = null;

    public function updatingFlowNameFilter(): void
    {
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
            'operationMessage' => $this->operationMessage,
        ]);
    }
}
