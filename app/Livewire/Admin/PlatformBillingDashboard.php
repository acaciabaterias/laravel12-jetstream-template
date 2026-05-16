<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\PlanoComercial;
use App\Services\Billing\PlatformBillingSummaryService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PlatformBillingDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url(as: 'plano')]
    public string $planFilter = 'all';

    #[Url(as: 'risco')]
    public string $riskFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPlanFilter(): void
    {
        $this->resetPage();
    }

    public function updatingRiskFilter(): void
    {
        $this->resetPage();
    }

    public function render(PlatformBillingSummaryService $platformBillingSummaryService)
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-billing');

        return view('livewire.admin.platform-billing-dashboard', [
            'summary' => $platformBillingSummaryService->summarize(),
            'subscriptions' => $platformBillingSummaryService->subscribers([
                'search' => $this->search,
                'status' => $this->statusFilter,
                'plan' => $this->planFilter,
                'risk' => $this->riskFilter,
            ]),
            'availablePlans' => PlanoComercial::query()
                ->where('ativo', true)
                ->orderBy('nome')
                ->get(),
        ]);
    }
}
