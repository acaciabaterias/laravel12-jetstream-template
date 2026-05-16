<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\InsightRiscoComercial;
use App\Models\MetricaPerformanceCanal;
use App\Models\RecorteCoorteComercial;
use App\Models\SnapshotAnalyticsComercial;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PlatformCommercialAnalyticsSummaryService
{
    public function __construct(
        private readonly CommercialAnalyticsSnapshotService $commercialAnalyticsSnapshotService,
    ) {}

    public function latestOrRebuild(int $days): SnapshotAnalyticsComercial
    {
        return SnapshotAnalyticsComercial::query()
            ->with(['cohorts', 'channelMetrics', 'riskInsights'])
            ->latest('reference_date')
            ->first() ?? $this->commercialAnalyticsSnapshotService->rebuild(days: $days);
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function summarize(SnapshotAnalyticsComercial $snapshotAnalyticsComercial): array
    {
        return [
            'reference_date' => $snapshotAnalyticsComercial->reference_date?->toDateString(),
            'mrr_amount' => round((float) $snapshotAnalyticsComercial->mrr_amount, 2),
            'churn_count' => $snapshotAnalyticsComercial->churn_count,
            'churn_rate' => round((float) $snapshotAnalyticsComercial->churn_rate, 4),
            'delinquent_count' => $snapshotAnalyticsComercial->delinquent_count,
            'recovered_count' => $snapshotAnalyticsComercial->recovered_count,
            'recovered_amount' => round((float) $snapshotAnalyticsComercial->recovered_amount, 2),
            'blocked_count' => $snapshotAnalyticsComercial->blocked_count,
        ];
    }

    public function cohorts(SnapshotAnalyticsComercial $snapshotAnalyticsComercial, string $search = ''): LengthAwarePaginator
    {
        $query = RecorteCoorteComercial::query()
            ->where('snapshot_analytics_comercial_id', $snapshotAnalyticsComercial->id)
            ->latest('cohort_start_date');

        if ($search !== '') {
            $query->where('cohort_label', 'like', '%'.$search.'%');
        }

        return $query->paginate(10);
    }

    public function channels(SnapshotAnalyticsComercial $snapshotAnalyticsComercial, string $type = 'all'): LengthAwarePaginator
    {
        $query = MetricaPerformanceCanal::query()
            ->where('snapshot_analytics_comercial_id', $snapshotAnalyticsComercial->id)
            ->orderByDesc('conversion_rate');

        if ($type !== 'all') {
            $query->where('channel_type', $type);
        }

        return $query->paginate(10, ['*'], 'channelsPage');
    }

    public function risks(SnapshotAnalyticsComercial $snapshotAnalyticsComercial, string $riskType = 'all'): LengthAwarePaginator
    {
        $query = InsightRiscoComercial::query()
            ->where('snapshot_analytics_comercial_id', $snapshotAnalyticsComercial->id)
            ->orderByDesc('total_exposure');

        if ($riskType !== 'all') {
            $query->where('risk_type', $riskType);
        }

        return $query->paginate(10, ['*'], 'risksPage');
    }
}
