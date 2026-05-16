<?php

declare(strict_types=1);

namespace App\Services\Billing;

class PlatformBillingInspectionService
{
    public function __construct(
        private readonly PlatformBillingSummaryService $platformBillingSummaryService,
    ) {}

    /**
     * @param  array{search?:string|null,status?:string|null,plan?:string|null,risk?:string|null,limit?:int|null}  $filters
     * @return array{summary: array<string, int|float>, subscriptions: array<int, array<string, mixed>>}
     */
    public function inspect(array $filters = []): array
    {
        $subscriptions = $this->platformBillingSummaryService
            ->subscribers([
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? 'all'),
                'plan' => (string) ($filters['plan'] ?? 'all'),
                'risk' => (string) ($filters['risk'] ?? 'all'),
            ], (int) ($filters['limit'] ?? 25));

        return [
            'summary' => $this->platformBillingSummaryService->summarize(),
            'subscriptions' => $subscriptions->getCollection()->map(function ($subscription): array {
                return [
                    'id' => $subscription->id,
                    'tenant_id' => $subscription->cliente_id,
                    'tenant_name' => $subscription->cliente->razao_social,
                    'tenant_subdomain' => $subscription->cliente->subdominio,
                    'plan' => $subscription->plano->slug,
                    'status' => $subscription->status,
                    'grace_ends_at' => $subscription->grace_ends_at?->toDateString(),
                    'reactivated_at' => $subscription->reactivated_at?->toIso8601String(),
                    'next_cycle_at' => $subscription->data_proximo_ciclo?->toDateString(),
                    'billing_blocked' => (bool) $subscription->cliente->billing_blocked,
                ];
            })->values()->all(),
        ];
    }
}
