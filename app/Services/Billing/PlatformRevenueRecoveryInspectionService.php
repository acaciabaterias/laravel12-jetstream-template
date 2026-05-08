<?php

declare(strict_types=1);

namespace App\Services\Billing;

class PlatformRevenueRecoveryInspectionService
{
    public function __construct(
        private readonly PlatformRevenueRecoverySummaryService $platformRevenueRecoverySummaryService,
    ) {}

    /**
     * @param  array{search?:string|null,status?:string|null,stage?:string|null,severity?:string|null,owner?:string|null,limit?:int|null}  $filters
     * @return array{summary: array<string, int|float>, cases: array<int, array<string, mixed>>}
     */
    public function inspect(array $filters = []): array
    {
        $cases = $this->platformRevenueRecoverySummaryService->cases([
            'search' => (string) ($filters['search'] ?? ''),
            'status' => (string) ($filters['status'] ?? 'all'),
            'stage' => (string) ($filters['stage'] ?? 'all'),
            'severity' => (string) ($filters['severity'] ?? 'all'),
            'owner' => (string) ($filters['owner'] ?? 'all'),
        ], (int) ($filters['limit'] ?? 25));

        return [
            'summary' => $this->platformRevenueRecoverySummaryService->summarize(),
            'cases' => $cases->getCollection()->map(function ($case): array {
                return [
                    'id' => $case->id,
                    'invoice_id' => $case->fatura_saas_id,
                    'tenant_id' => $case->cliente_id,
                    'tenant_name' => $case->cliente->razao_social,
                    'status' => $case->status->value,
                    'stage_name' => $case->current_stage,
                    'severity' => $case->severity->value,
                    'owner' => $case->owner?->name,
                    'reengagement_eligible' => $this->platformRevenueRecoverySummaryService->isEligibleForReengagement($case),
                ];
            })->values()->all(),
        ];
    }
}
