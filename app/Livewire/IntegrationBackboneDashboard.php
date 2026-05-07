<?php

namespace App\Livewire;

use App\Models\ContratoEvento;
use App\Models\EntregaIntegracao;
use App\Models\EventoOutbox;
use App\Services\Integration\IntegrationMetrics;
use App\Support\Integration\IntegrationFlowStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Component;

class IntegrationBackboneDashboard extends Component
{
    public string $eventTypeFilter = '';

    public string $statusFilter = '';

    public function mount(): void
    {
        Gate::authorize('view-integration-operations');
    }

    public function render(): View
    {
        $metricsService = app(IntegrationMetrics::class);
        $metrics = $metricsService->syncOperationalSnapshot();
        $hasBackboneTables = $metricsService->hasBackboneTables();

        $deliveries = new Collection;
        $outboxes = new Collection;
        $contractsCount = 0;

        if ($hasBackboneTables) {
            $deliveries = EntregaIntegracao::query()
                ->with('entregavel')
                ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
                ->latest('id')
                ->limit(8)
                ->get();

            $outboxQuery = EventoOutbox::query();

            if ($this->eventTypeFilter !== '') {
                $outboxQuery->where('event_type', $this->eventTypeFilter);
            }

            $outboxes = $outboxQuery->latest('id')->limit(8)->get();
            $contractsCount = ContratoEvento::query()->count();
        }

        return view('livewire.integration-backbone-dashboard', [
            'outboxes' => $outboxes,
            'deadLetters' => $metrics['outboxes'][IntegrationFlowStatus::DeadLetter->value] ?? 0,
            'pendingEvents' => $metrics['outboxes'][IntegrationFlowStatus::Pending->value] ?? 0,
            'contractsCount' => $contractsCount,
            'deliveries' => $deliveries,
            'metrics' => $metrics,
        ]);
    }
}
