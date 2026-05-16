<?php

declare(strict_types=1);

namespace App\Services\Operations;

use App\Models\OperationalAlertSnapshot;
use App\Support\Operations\OperationalSeverity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductionObservabilitySummaryService
{
    public function __construct(
        private readonly OperationalHealthSnapshotService $operationalHealthSnapshotService,
    ) {}

    /**
     * @return array<string, int>
     */
    public function summarize(): array
    {
        return [
            'healthy' => OperationalAlertSnapshot::query()->where('severity', OperationalSeverity::Healthy->value)->count(),
            'warning' => OperationalAlertSnapshot::query()->where('severity', OperationalSeverity::Warning->value)->count(),
            'critical' => OperationalAlertSnapshot::query()->where('severity', OperationalSeverity::Critical->value)->count(),
            'unavailable_collectors' => OperationalAlertSnapshot::query()->where('status', 'unavailable')->count(),
        ];
    }

    public function latestOrRebuild(): array
    {
        $latest = OperationalAlertSnapshot::query()
            ->latest('reference_at')
            ->limit(4)
            ->get();

        if ($latest->isNotEmpty()) {
            return $latest->all();
        }

        return $this->operationalHealthSnapshotService->rebuild();
    }

    /**
     * @param array{flow_name?:string, severity?:string, status?:string} $filters
     */
    public function snapshots(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return OperationalAlertSnapshot::query()
            ->when(($filters['flow_name'] ?? '') !== '', fn ($query) => $query->where('flow_name', $filters['flow_name']))
            ->when(($filters['severity'] ?? '') !== '', fn ($query) => $query->where('severity', $filters['severity']))
            ->when(($filters['status'] ?? '') !== '', fn ($query) => $query->where('status', $filters['status']))
            ->latest('reference_at')
            ->paginate($perPage);
    }
}
