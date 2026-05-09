<?php

declare(strict_types=1);

namespace App\Services\Operations;

class ProductionObservabilityInspectionService
{
    public function __construct(
        private readonly ProductionObservabilitySummaryService $productionObservabilitySummaryService,
    ) {}

    /**
     * @param array{flow_name?:string|null, severity?:string|null, status?:string|null, limit?:int|null} $filters
     * @return array{summary: array<string, int>, snapshots: array<int, array<string, mixed>>}
     */
    public function inspect(array $filters = []): array
    {
        $snapshots = $this->productionObservabilitySummaryService->snapshots([
            'flow_name' => (string) ($filters['flow_name'] ?? ''),
            'severity' => (string) ($filters['severity'] ?? ''),
            'status' => (string) ($filters['status'] ?? ''),
        ], (int) ($filters['limit'] ?? 25));

        return [
            'summary' => $this->productionObservabilitySummaryService->summarize(),
            'snapshots' => $snapshots->getCollection()->map(fn ($snapshot): array => [
                'id' => $snapshot->id,
                'flow_name' => $snapshot->flow_name,
                'status' => $snapshot->status->value,
                'severity' => $snapshot->severity->value,
                'backlog_count' => $snapshot->backlog_count,
                'latency_ms' => $snapshot->latency_ms,
                'failure_rate' => (float) $snapshot->failure_rate,
                'open_replays' => $snapshot->open_replays,
                'reference_at' => $snapshot->reference_at?->toAtomString(),
            ])->values()->all(),
        ];
    }
}
