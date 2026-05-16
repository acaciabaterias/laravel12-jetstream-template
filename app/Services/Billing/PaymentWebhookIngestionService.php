<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Jobs\LogAuditJob;
use App\Models\CobrancaSaaSExterna;
use App\Models\GatewayCobrancaSaaS;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\PaymentReturnProcessingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        bool $forceReplay = false,
    ): RetornoPagamentoSaaS {
        return DB::connection('central')->transaction(function () use ($gatewayCobrancaSaaS, $payload, $sourceType, $actor, $forceReplay): RetornoPagamentoSaaS {
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

            $previousStatus = $retorno->processing_status;
            $previousError = $retorno->processing_error;

            if ($retorno->processing_status === PaymentReturnProcessingStatus::Processed && ! $forceReplay) {
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
                'processing_status' => PaymentReturnProcessingStatus::Pending->value,
                'processing_error' => null,
            ]);

            $this->paymentReconciliationService->reconcile($charge, $retorno->refresh(), $actor);

            $retorno->update([
                'processed_at' => now(),
                'processing_status' => PaymentReturnProcessingStatus::Processed->value,
                'processing_error' => null,
            ]);

            if ($forceReplay && $sourceType === 'manual_replay' && $actor !== null) {
                $this->recordReplayAudit($retorno->refresh(), $actor, $previousStatus, $previousError);
            }

            return $retorno->refresh();
        });
    }

    private function recordReplayAudit(
        RetornoPagamentoSaaS $retornoPagamentoSaaS,
        UsuarioPlataforma $actor,
        PaymentReturnProcessingStatus $previousStatus,
        ?string $previousError,
    ): void {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        LogAuditJob::dispatchSync([
            'user_id' => null,
            'action' => 'payment_return_replayed',
            'table_name' => 'retornos_pagamento_saas',
            'record_id' => $retornoPagamentoSaaS->id,
            'old_values' => [
                'processing_status' => $previousStatus->value,
                'processing_error' => $previousError,
                'source_type' => $retornoPagamentoSaaS->source_type,
            ],
            'new_values' => [
                'processing_status' => $retornoPagamentoSaaS->processing_status->value,
                'processing_error' => $retornoPagamentoSaaS->processing_error,
                'source_type' => 'manual_replay',
                'operator_user_id' => $actor->id,
                'gateway_id' => $retornoPagamentoSaaS->gateway_cobranca_saas_id,
                'external_reference' => $retornoPagamentoSaaS->external_reference,
                'external_event_id' => $retornoPagamentoSaaS->external_event_id,
            ],
        ]);
    }
}
