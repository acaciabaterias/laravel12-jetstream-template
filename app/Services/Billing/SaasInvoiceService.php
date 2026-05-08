<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\FaturaSaaS;
use App\Support\Billing\SaasInvoiceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaasInvoiceService
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createInvoice(AssinaturaPlataforma $assinatura, array $attributes = []): FaturaSaaS
    {
        return DB::connection('central')->transaction(function () use ($assinatura, $attributes): FaturaSaaS {
            $reference = (string) ($attributes['referencia'] ?? now()->format('Y-m'));
            $dueDate = Carbon::parse($attributes['vencimento'] ?? now()->addDays(5))->startOfDay();

            return FaturaSaaS::query()->create([
                'assinatura_id' => $assinatura->id,
                'cliente_id' => $assinatura->cliente_id,
                'referencia' => $reference,
                'vencimento' => $dueDate->toDateString(),
                'valor' => (float) ($attributes['valor'] ?? $assinatura->plano->preco_mensal ?? 0),
                'valor_pago' => $attributes['valor_pago'] ?? null,
                'status' => $attributes['status'] ?? SaasInvoiceStatus::Pending->value,
                'external_invoice_id' => $attributes['external_invoice_id'] ?? null,
                'billing_channel' => $attributes['billing_channel'] ?? 'manual',
                'paid_at' => $attributes['paid_at'] ?? null,
                'payload_gateway' => $attributes['payload_gateway'] ?? ['provider' => 'manual'],
                'metadata' => $attributes['metadata'] ?? ['source' => 'platform_billing'],
            ]);
        });
    }

    public function markAsOverdue(FaturaSaaS $faturaSaaS, ?Carbon $referenceDate = null): FaturaSaaS
    {
        $referenceDate ??= now();

        if ($faturaSaaS->status === SaasInvoiceStatus::Paid->value) {
            return $faturaSaaS;
        }

        if ($referenceDate->startOfDay()->lte($faturaSaaS->vencimento->startOfDay())) {
            return $faturaSaaS;
        }

        $faturaSaaS->update([
            'status' => SaasInvoiceStatus::Overdue->value,
        ]);

        return $faturaSaaS->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function markAsPaid(FaturaSaaS $faturaSaaS, array $attributes = []): FaturaSaaS
    {
        $paidAt = Carbon::parse($attributes['paid_at'] ?? now());
        $amountPaid = $attributes['valor_pago'] ?? $faturaSaaS->valor;

        $faturaSaaS->update([
            'status' => SaasInvoiceStatus::Paid->value,
            'valor_pago' => $amountPaid,
            'paid_at' => $paidAt,
        ]);

        return $faturaSaaS->refresh();
    }
}
