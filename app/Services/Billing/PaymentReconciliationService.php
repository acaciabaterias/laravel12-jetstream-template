<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CobrancaSaaSExterna;
use App\Models\ConciliacaoPagamentoSaaS;
use App\Models\ExcecaoConciliacaoSaaS;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\ExternalChargeStatus;
use App\Support\Billing\PaymentEventType;
use App\Support\Billing\PaymentExceptionStatus;
use App\Support\Billing\PaymentReconciliationStatus;
use Illuminate\Support\Facades\DB;

class PaymentReconciliationService
{
    private readonly SaasInvoiceService $saasInvoiceService;

    private readonly DelinquencyPolicyEvaluator $delinquencyPolicyEvaluator;

    private readonly PlatformPaymentsEventPublisher $eventPublisher;

    public function __construct(
        ?SaasInvoiceService $saasInvoiceService = null,
        ?DelinquencyPolicyEvaluator $delinquencyPolicyEvaluator = null,
        ?PlatformPaymentsEventPublisher $eventPublisher = null,
    ) {
        $this->saasInvoiceService = $saasInvoiceService ?? new SaasInvoiceService;
        $this->delinquencyPolicyEvaluator = $delinquencyPolicyEvaluator ?? new DelinquencyPolicyEvaluator;
        $this->eventPublisher = $eventPublisher ?? app(PlatformPaymentsEventPublisher::class);
    }

    /**
     * @return array{status: PaymentReconciliationStatus, difference: float, reason: string|null}
     */
    public function determineOutcome(CobrancaSaaSExterna $cobrancaSaaSExterna, float $receivedAmount, ?string $externalReference): array
    {
        if ($externalReference !== null && $externalReference !== $cobrancaSaaSExterna->external_reference) {
            return [
                'status' => PaymentReconciliationStatus::Exception,
                'difference' => $receivedAmount - (float) $cobrancaSaaSExterna->valor_emitido,
                'reason' => 'reference_mismatch',
            ];
        }

        $difference = round($receivedAmount - (float) $cobrancaSaaSExterna->valor_emitido, 2);

        if (abs($difference) > 0.009) {
            return [
                'status' => PaymentReconciliationStatus::Exception,
                'difference' => $difference,
                'reason' => 'amount_mismatch',
            ];
        }

        return [
            'status' => PaymentReconciliationStatus::Matched,
            'difference' => 0.0,
            'reason' => null,
        ];
    }

    public function reconcile(
        CobrancaSaaSExterna $cobrancaSaaSExterna,
        RetornoPagamentoSaaS $retornoPagamentoSaaS,
        ?UsuarioPlataforma $actor = null,
    ): ConciliacaoPagamentoSaaS {
        return DB::connection('central')->transaction(function () use ($cobrancaSaaSExterna, $retornoPagamentoSaaS, $actor): ConciliacaoPagamentoSaaS {
            $charge = CobrancaSaaSExterna::query()
                ->with(['fatura.assinatura', 'fatura.cliente'])
                ->findOrFail($cobrancaSaaSExterna->id);

            $receivedAmount = (float) data_get($retornoPagamentoSaaS->payload, 'amount', data_get($retornoPagamentoSaaS->payload, 'valor', 0));
            $externalReference = (string) ($retornoPagamentoSaaS->external_reference ?? data_get($retornoPagamentoSaaS->payload, 'external_reference', $charge->external_reference));
            $outcome = $this->determineOutcome($charge, $receivedAmount, $externalReference);

            $conciliacao = ConciliacaoPagamentoSaaS::query()->create([
                'fatura_saas_id' => $charge->fatura_saas_id,
                'cobranca_saas_externa_id' => $charge->id,
                'retorno_pagamento_saas_id' => $retornoPagamentoSaaS->id,
                'status' => $outcome['status']->value,
                'reconciliation_type' => 'automatic',
                'expected_amount' => $charge->valor_emitido,
                'received_amount' => $receivedAmount,
                'difference_amount' => $outcome['difference'],
                'reconciled_at' => now(),
                'operator_user_id' => $actor?->id,
                'notes' => $outcome['reason'],
                'metadata' => ['source' => 'webhook'],
            ]);

            if ($outcome['status'] === PaymentReconciliationStatus::Matched) {
                $charge->update([
                    'status' => ExternalChargeStatus::Paid->value,
                    'paid_at' => now(),
                ]);

                $invoice = $this->saasInvoiceService->markAsPaid($charge->fatura, [
                    'paid_at' => now(),
                    'valor_pago' => $receivedAmount,
                ]);

                $this->delinquencyPolicyEvaluator->assess($invoice->assinatura()->firstOrFail(), now());

                $this->eventPublisher->publish(
                    eventType: 'COBRANCA_SAAS_LIQUIDADA',
                    faturaSaaS: $invoice->refresh()->loadMissing('cliente'),
                    payload: [
                        'invoice_id' => $invoice->id,
                        'tenant_id' => $invoice->cliente_id,
                        'charge_id' => $charge->id,
                        'received_amount' => $receivedAmount,
                        'external_reference' => $charge->external_reference,
                    ],
                    consumers: config('platform_payments.events.default_consumers', ['platform', 'analytics']),
                    schemaDefinition: [
                        'invoice_id' => 'integer',
                        'tenant_id' => 'integer',
                        'charge_id' => 'integer',
                        'received_amount' => 'decimal',
                        'external_reference' => 'string',
                    ],
                );

                return $conciliacao->refresh();
            }

            ExcecaoConciliacaoSaaS::query()->create([
                'fatura_saas_id' => $charge->fatura_saas_id,
                'cobranca_saas_externa_id' => $charge->id,
                'retorno_pagamento_saas_id' => $retornoPagamentoSaaS->id,
                'conciliacao_pagamento_saas_id' => $conciliacao->id,
                'status' => PaymentExceptionStatus::Open->value,
                'exception_type' => $outcome['reason'] ?? 'unknown',
                'severity' => 'high',
                'impact_on_subscription' => 'review_block',
                'opened_at' => now(),
                'owner_user_id' => $actor?->id,
                'resolution_notes' => null,
                'metadata' => ['source' => 'webhook'],
            ]);

            $this->eventPublisher->publish(
                eventType: 'CONCILIACAO_SAAS_PENDENTE',
                faturaSaaS: $charge->fatura->refresh()->loadMissing('cliente'),
                payload: [
                    'invoice_id' => $charge->fatura_saas_id,
                    'tenant_id' => $charge->fatura->cliente_id,
                    'charge_id' => $charge->id,
                    'difference_amount' => $outcome['difference'],
                    'reason' => $outcome['reason'],
                ],
                consumers: ['platform'],
                schemaDefinition: [
                    'invoice_id' => 'integer',
                    'tenant_id' => 'integer',
                    'charge_id' => 'integer',
                    'difference_amount' => 'decimal',
                    'reason' => 'string|null',
                ],
            );

            return $conciliacao->refresh();
        });
    }
}
