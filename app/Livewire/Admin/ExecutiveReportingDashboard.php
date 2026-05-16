<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\ExecutiveReportExport;
use App\Services\Billing\ExecutiveReportExportService;
use App\Services\Billing\ExecutiveReportingInspectionService;
use App\Services\Billing\ExecutiveReportingSummaryService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.admin')]
class ExecutiveReportingDashboard extends Component
{
    #[Url(as: 'dias')]
    public int $periodDays = 30;

    #[Url(as: 'plano')]
    public string $planFilter = 'all';

    #[Url(as: 'canal')]
    public string $channelFilter = 'all';

    #[Url(as: 'carteira')]
    public string $portfolioFilter = 'all';

    #[Url(as: 'recovery')]
    public string $recoveryStatusFilter = 'all';

    #[Url(as: 'formato')]
    public string $exportFormatFilter = 'all';

    #[Url(as: 'status')]
    public string $exportStatusFilter = 'all';

    public ?string $operationMessage = null;

    public function captureSnapshot(ExecutiveReportingSummaryService $executiveReportingSummaryService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-executive-reporting');

        $executiveReportingSummaryService->capture($this->currentFilters(), auth('platform')->id());
        $this->operationMessage = 'Snapshot executivo atualizado com sucesso.';
    }

    public function exportExcel(ExecutiveReportExportService $executiveReportExportService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-executive-reporting');

        $export = $executiveReportExportService->export($this->currentFilters(), 'excel', auth('platform')->id());
        $this->operationMessage = sprintf('Relatorio Excel %d gerado com sucesso.', $export->id);
    }

    public function exportPdf(ExecutiveReportExportService $executiveReportExportService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-executive-reporting');

        $export = $executiveReportExportService->export($this->currentFilters(), 'pdf', auth('platform')->id());
        $this->operationMessage = sprintf('Relatorio PDF %d gerado com sucesso.', $export->id);
    }

    public function reexecuteExport(int $exportId, ExecutiveReportExportService $executiveReportExportService): void
    {
        Gate::forUser(auth('platform')->user())->authorize('manage-executive-reporting');

        $export = ExecutiveReportExport::query()->with('snapshot', 'definition')->findOrFail($exportId);
        $reexecuted = $executiveReportExportService->reexecute($export, auth('platform')->id());
        $this->operationMessage = sprintf('Relatorio %d reexecutado como %d.', $export->id, $reexecuted->id);
    }

    public function render(
        ExecutiveReportingInspectionService $executiveReportingInspectionService,
        ExecutiveReportingSummaryService $executiveReportingSummaryService,
    ): View {
        Gate::forUser(auth('platform')->user())->authorize('manage-executive-reporting');

        $inspection = $executiveReportingInspectionService->inspect(array_merge($this->currentFilters(), [
            'format' => $this->exportFormatFilter,
            'status' => $this->exportStatusFilter,
            'limit' => 12,
        ]));

        return view('livewire.admin.executive-reporting-dashboard', [
            'summary' => $inspection['summary'],
            'snapshot' => $inspection['snapshot'],
            'exports' => $inspection['exports'],
            'executionLogs' => $inspection['execution_logs'],
            'availablePlans' => $executiveReportingSummaryService->availablePlans(),
            'operationMessage' => $this->operationMessage,
        ]);
    }

    /**
     * @return array{days:int,plan:string,channel:string,portfolio:string,recovery_status:string}
     */
    private function currentFilters(): array
    {
        return [
            'days' => $this->periodDays,
            'plan' => $this->planFilter,
            'channel' => $this->channelFilter,
            'portfolio' => $this->portfolioFilter,
            'recovery_status' => $this->recoveryStatusFilter,
        ];
    }
}
