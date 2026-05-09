<?php

declare(strict_types=1);

namespace App\Services\Billing;

class PlatformCommercialAnalyticsInspectionService
{
    public function __construct(
        private readonly CommercialAnalyticsDrilldownService $commercialAnalyticsDrilldownService,
    ) {}

    /**
     * @param  array{snapshot_id?:int|string|null,metric_key?:string|null,dimension_type?:string|null,source_type?:string|null,limit?:int|null}  $filters
     * @return array{drilldowns: array<int, array<string, mixed>>}
     */
    public function inspect(array $filters = []): array
    {
        $drilldowns = $this->commercialAnalyticsDrilldownService->inspect($filters);

        return [
            'drilldowns' => $drilldowns->getCollection()->map(function ($drilldown): array {
                return [
                    'id' => $drilldown->id,
                    'snapshot_id' => $drilldown->snapshot_analytics_comercial_id,
                    'source_type' => $drilldown->source_type,
                    'source_id' => $drilldown->source_id,
                    'dimension_type' => $drilldown->dimension_type,
                    'dimension_value' => $drilldown->dimension_value,
                    'metric_key' => $drilldown->metric_key,
                    'metric_value' => (float) $drilldown->metric_value,
                    'metadata' => $drilldown->metadata ?? [],
                ];
            })->values()->all(),
        ];
    }
}
