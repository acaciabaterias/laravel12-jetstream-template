<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Billing\CommercialAnalyticsSnapshotService;
use App\Services\Billing\PlatformCommercialAnalyticsSummaryService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class PlatformCommercialAnalyticsDashboard extends Component
{
    use WithPagination;

    #[Url(as: 'dias')]
    public int $periodDays = 30;

    #[Url(as: 'coorte')]
    public string $cohortSearch = '';

    #[Url(as: 'canal')]
    public string $channelType = 'all';

    #[Url(as: 'risco')]
    public string $riskType = 'all';

    public function updatingCohortSearch(): void
    {
        $this->resetPage();
    }

    public function updatingChannelType(): void
    {
        $this->resetPage(pageName: 'channelsPage');
    }

    public function updatingRiskType(): void
    {
        $this->resetPage(pageName: 'risksPage');
    }

    public function rebuild(CommercialAnalyticsSnapshotService $commercialAnalyticsSnapshotService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-commercial-analytics');

        $commercialAnalyticsSnapshotService->rebuild(days: $this->periodDays);

        session()->flash('status', 'Snapshot comercial reconstruido com sucesso.');
    }

    public function render(PlatformCommercialAnalyticsSummaryService $platformCommercialAnalyticsSummaryService)
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-platform-commercial-analytics');

        $snapshot = $platformCommercialAnalyticsSummaryService->latestOrRebuild($this->periodDays);

        return view('livewire.admin.platform-commercial-analytics-dashboard', [
            'snapshot' => $snapshot,
            'summary' => $platformCommercialAnalyticsSummaryService->summarize($snapshot),
            'cohorts' => $platformCommercialAnalyticsSummaryService->cohorts($snapshot, $this->cohortSearch),
            'channels' => $platformCommercialAnalyticsSummaryService->channels($snapshot, $this->channelType),
            'risks' => $platformCommercialAnalyticsSummaryService->risks($snapshot, $this->riskType),
        ]);
    }
}
