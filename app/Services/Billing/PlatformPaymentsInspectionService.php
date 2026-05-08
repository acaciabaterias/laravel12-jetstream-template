<?php

declare(strict_types=1);

namespace App\Services\Billing;

class PlatformPaymentsInspectionService
{
    public function __construct(
        private readonly PlatformPaymentsSummaryService $platformPaymentsSummaryService,
    ) {}

    /**
     * @param  array{search?:string|null,status?:string|null,channel?:string|null,exception?:string|null,limit?:int|null}  $filters
     * @return array{summary: array<string, int|float>, charges: array<int, array<string, mixed>>}
     */
    public function inspect(array $filters = []): array
    {
        $charges = $this->platformPaymentsSummaryService
            ->charges([
                'search' => (string) ($filters['search'] ?? ''),
                'status' => (string) ($filters['status'] ?? 'all'),
                'channel' => (string) ($filters['channel'] ?? 'all'),
                'exception' => (string) ($filters['exception'] ?? 'all'),
            ], (int) ($filters['limit'] ?? 25));

        return [
            'summary' => $this->platformPaymentsSummaryService->summarize(),
            'charges' => $charges->getCollection()->map(function ($charge): array {
                return [
                    'id' => $charge->id,
                    'invoice_id' => $charge->fatura_saas_id,
                    'tenant_id' => $charge->fatura->cliente_id,
                    'tenant_name' => $charge->fatura->cliente->razao_social,
                    'external_reference' => $charge->external_reference,
                    'status' => $charge->status->value,
                    'payment_channel' => $charge->payment_channel,
                    'amount' => (float) $charge->valor_emitido,
                    'exceptions' => $charge->fatura->excecoesConciliacao()
                        ->latest()
                        ->get()
                        ->map(fn ($exception): array => [
                            'id' => $exception->id,
                            'type' => $exception->exception_type,
                            'status' => $exception->status->value,
                            'severity' => $exception->severity,
                        ])->all(),
                ];
            })->values()->all(),
        ];
    }
}
