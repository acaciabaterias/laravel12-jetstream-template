<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use App\Services\Billing\PaymentWebhookIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ProcessPlatformPaymentReturnJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $retornoPagamentoSaaSId,
        public ?int $operatorUserId = null,
    ) {}

    public function handle(PaymentWebhookIngestionService $paymentWebhookIngestionService): void
    {
        $retorno = RetornoPagamentoSaaS::query()
            ->with(['gateway'])
            ->find($this->retornoPagamentoSaaSId);

        if (! $retorno) {
            return;
        }

        $operator = $this->operatorUserId !== null
            ? UsuarioPlataforma::query()->find($this->operatorUserId)
            : null;

        $paymentWebhookIngestionService->ingest(
            gatewayCobrancaSaaS: $retorno->gateway,
            payload: array_merge((array) $retorno->payload, [
                'external_reference' => $retorno->external_reference,
                'external_event_id' => $retorno->external_event_id,
                'idempotency_key' => $retorno->idempotency_key,
                'event_type' => $retorno->event_type,
            ]),
            sourceType: 'manual_replay',
            actor: $operator,
            forceReplay: true,
        );
    }
}
