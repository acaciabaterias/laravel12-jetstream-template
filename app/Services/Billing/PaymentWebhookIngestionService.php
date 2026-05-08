<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CobrancaSaaSExterna;
use App\Models\GatewayCobrancaSaaS;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\PaymentReturnProcessingStatus;
use Illuminate\Support\Facades\DB;

class PaymentWebhookIngestionService
{
    private readonly PaymentReconciliationService $paymentReconciliationService;

    public function __construct(?PaymentReconciliationService $paymentReconciliationService = null)
    {
        $this->paymentReconciliationService = $paymentReconciliationService ?? new PaymentReconciliationService;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function ingest(
        GatewayCobrancaSaaS $gatewayCobrancaSaaS,
        array $payload,
        string $sourceType = 'webhook',
        ?UsuarioPlataforma $actor = null,
    ): RetornoPagamentoSaaS {
        return DB::connection('central')->transaction(function () use ($gatewayCobrancaSaaS, $payload, $sourceType, $actor): RetornoPagamentoSaaS {
            $externalReference = (string) ($payload['external_reference'] ?? '');
            $eventType = (string) ($payload['event_type'] ?? 'payment_received');
            $idempotencyKey = (string) ($payload['idempotency_key'] ?? sha1(json_encode([
                'gateway_id' => $gatewayCobrancaSaaS->id,
                'external_event_id' => $payload['external_event_id'] ?? null,
                'external_reference' => $externalReference,
                'event_type' => $eventType,
            ])));

            $retorno = RetornoPagamentoSaaS::query()->firstOrCreate(
                [
                    'gateway_cobranca_saas_id' => $gatewayCobrancaSaaS->id,
                    'idempotency_key' => $idempotencyKey,
                ],
                [
                    'cobranca_saas_externa_id' => null,
                    'source_type' => $sourceType,
                    'external_event_id' => (string) ($payload['external_event_id'] ?? ''),
                    'external_reference' => $externalReference !== '' ? $externalReference : null,
                    'event_type' => $eventType,
                    'payload' => $payload,
                    'received_at' => now(),
                    'processing_status' => PaymentReturnProcessingStatus::Pending->value,
                    'processing_error' => null,
                    'metadata' => ['source' => $sourceType],
                ],
            );

            if ($retorno->processing_status === PaymentReturnProcessingStatus::Processed) {
                return $retorno;
            }

            $charge = CobrancaSaaSExterna::query()
                ->where('gateway_cobranca_saas_id', $gatewayCobrancaSaaS->id)
                ->where(function ($query) use ($payload, $externalReference): void {
                    if ($externalReference !== '') {
                        $query->orWhere('external_reference', $externalReference);
                    }

                    if (isset($payload['external_charge_id'])) {
                        $query->orWhere('external_charge_id', (string) $payload['external_charge_id']);
                    }
                })
                ->first();

            if ($charge === null) {
                $retorno->update([
                    'processed_at' => now(),
                    'processing_status' => PaymentReturnProcessingStatus::Failed->value,
                    'processing_error' => 'external_charge_not_found',
                ]);

                return $retorno->refresh();
            }

            $retorno->update([
                'cobranca_saas_externa_id' => $charge->id,
            ]);

            $this->paymentReconciliationService->reconcile($charge, $retorno->refresh(), $actor);

            $retorno->update([
                'processed_at' => now(),
                'processing_status' => PaymentReturnProcessingStatus::Processed->value,
                'processing_error' => null,
            ]);

            return $retorno->refresh();
        });
    }
}
