<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Billing\PlatformPaymentsSummaryService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PlatformPaymentsDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'busca')]
    public string $search = '';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url(as: 'canal')]
    public string $channelFilter = 'all';

    #[Url(as: 'excecao')]
    public string $exceptionFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingChannelFilter(): void
    {
        $this->resetPage();
    }

    public function updatingExceptionFilter(): void
    {
        $this->resetPage();
    }

    public function render(PlatformPaymentsSummaryService $platformPaymentsSummaryService)
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-payments');

        return view('livewire.admin.platform-payments-dashboard', [
            'summary' => $platformPaymentsSummaryService->summarize(),
            'charges' => $platformPaymentsSummaryService->charges([
                'search' => $this->search,
                'status' => $this->statusFilter,
                'channel' => $this->channelFilter,
                'exception' => $this->exceptionFilter,
            ]),
        ]);
    }
}
