<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Models\UsuarioPlataforma;
use App\Support\Billing\ExternalChargeStatus;
use App\Support\Billing\PaymentEventType;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExternalChargeIssuanceService
{
    public function __construct(
        private readonly PlatformPaymentsEventPublisher $eventPublisher,
    ) {}

    public function issue(
        FaturaSaaS $faturaSaaS,
        GatewayCobrancaSaaS $gatewayCobrancaSaaS,
        string $paymentChannel,
        ?UsuarioPlataforma $actor = null,
        bool $forceReissue = false,
        ?string $reason = null,
    ): CobrancaSaaSExterna {
        return DB::connection('central')->transaction(function () use (
            $faturaSaaS,
            $gatewayCobrancaSaaS,
            $paymentChannel,
            $actor,
            $forceReissue,
            $reason
        ): CobrancaSaaSExterna {
            $invoice = FaturaSaaS::query()
                ->with(['cliente', 'assinatura', 'cobrancasExternas'])
                ->findOrFail($faturaSaaS->id);

            $existingCharge = $invoice->cobrancasExternas()
                ->whereIn('status', [
                    ExternalChargeStatus::Draft->value,
                    ExternalChargeStatus::Submitted->value,
                    ExternalChargeStatus::Pending->value,
                    ExternalChargeStatus::Paid->value,
                ])
                ->latest('id')
                ->first();

            if ($existingCharge !== null && ! $forceReissue) {
                return $existingCharge;
            }

            $sequence = ($invoice->cobrancasExternas()->count()) + 1;
            $issuedAt = now();
            $charge = CobrancaSaaSExterna::query()->create([
                'fatura_saas_id' => $invoice->id,
                'gateway_cobranca_saas_id' => $gatewayCobrancaSaaS->id,
                'external_charge_id' => $this->generateExternalChargeId($invoice, $gatewayCobrancaSaaS, $sequence),
                'external_reference' => sprintf('saas-%s-%s', $invoice->id, $sequence),
                'payment_channel' => $paymentChannel,
                'status' => ExternalChargeStatus::Submitted->value,
                'valor_emitido' => $invoice->valor,
                'vencimento_emitido' => $invoice->vencimento,
                'issued_at' => $issuedAt,
                'idempotency_key' => $this->buildIdempotencyKey($invoice, $gatewayCobrancaSaaS, $paymentChannel, $sequence),
                'metadata' => [
                    'issued_by_user_id' => $actor?->id,
                    'reason' => $reason,
                    'force_reissue' => $forceReissue,
                    'reissued_from_charge_id' => $existingCharge?->id,
                ],
            ]);

            $invoice->update([
                'external_invoice_id' => $charge->external_charge_id,
                'billing_channel' => $paymentChannel,
            ]);

            $this->eventPublisher->publish(
                eventType: 'COBRANCA_SAAS_EMITIDA',
                faturaSaaS: $invoice->refresh()->loadMissing('cliente'),
                payload: [
                    'invoice_id' => $invoice->id,
                    'tenant_id' => $invoice->cliente_id,
                    'charge_id' => $charge->id,
                    'external_reference' => $charge->external_reference,
                    'payment_channel' => $paymentChannel,
                    'issued_at' => Carbon::parse($charge->issued_at)->toIso8601String(),
                ],
                consumers: config('platform_payments.events.default_consumers', ['platform', 'analytics']),
                schemaDefinition: [
                    'invoice_id' => 'integer',
                    'tenant_id' => 'integer',
                    'charge_id' => 'integer',
                    'external_reference' => 'string',
                    'payment_channel' => 'string',
                    'issued_at' => 'datetime',
                ],
            );

            return $charge->refresh();
        });
    }

    public function buildIdempotencyKey(
        FaturaSaaS $faturaSaaS,
        GatewayCobrancaSaaS $gatewayCobrancaSaaS,
        string $paymentChannel,
        int $sequence = 1,
    ): string {
        return sha1(sprintf(
            'payments:%s:%s:%s:%s:%s',
            $faturaSaaS->id,
            $gatewayCobrancaSaaS->id,
            $paymentChannel,
            $faturaSaaS->referencia,
            $sequence
        ));
    }

    private function generateExternalChargeId(
        FaturaSaaS $faturaSaaS,
        GatewayCobrancaSaaS $gatewayCobrancaSaaS,
        int $sequence,
    ): string {
        return sprintf('ext-%s-%s-%s', $gatewayCobrancaSaaS->slug, $faturaSaaS->id, $sequence);
    }
}
