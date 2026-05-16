<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Billing\PlatformRevenueRecoverySummaryService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PlatformRevenueRecoveryDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url(as: 'estagio')]
    public string $stageFilter = 'all';

    #[Url(as: 'severidade')]
    public string $severityFilter = 'all';

    #[Url(as: 'responsavel')]
    public string $ownerFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStageFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSeverityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOwnerFilter(): void
    {
        $this->resetPage();
    }

    public function render(PlatformRevenueRecoverySummaryService $platformRevenueRecoverySummaryService)
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-revenue-recovery');

        return view('livewire.admin.platform-revenue-recovery-dashboard', [
            'summary' => $platformRevenueRecoverySummaryService->summarize(),
            'cases' => $platformRevenueRecoverySummaryService->cases([
                'search' => $this->search,
                'status' => $this->statusFilter,
                'stage' => $this->stageFilter,
                'severity' => $this->severityFilter,
                'owner' => $this->ownerFilter,
            ]),
        ]);
    }
}
