<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\PlanoComercial;
use Illuminate\Support\Collection;

class ExecutiveReportingSummaryService
{
    public function __construct(
        private readonly ExecutiveAnalyticsSnapshotService $executiveAnalyticsSnapshotService,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function latestOrCapture(array $filters = [], ?int $generatedBy = null): ExecutiveAnalyticsSnapshot
    {
        return $this->executiveAnalyticsSnapshotService->latestOrCapture($filters, $generatedBy);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function capture(array $filters = [], ?int $generatedBy = null): ExecutiveAnalyticsSnapshot
    {
        return $this->executiveAnalyticsSnapshotService->capture($filters, $generatedBy);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}
     */
    public function normalizeFilters(array $filters = []): array
    {
        return $this->executiveAnalyticsSnapshotService->normalizeFilters($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function summarize(ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot): array
    {
        return $executiveAnalyticsSnapshot->kpi_payload ?? [];
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function drilldowns(ExecutiveAnalyticsSnapshot $executiveAnalyticsSnapshot): array
    {
        return $executiveAnalyticsSnapshot->drilldown_payload ?? [];
    }

    /**
     * @return Collection<int, PlanoComercial>
     */
    public function availablePlans(): Collection
    {
        return PlanoComercial::query()->orderBy('nome')->get();
    }
}
