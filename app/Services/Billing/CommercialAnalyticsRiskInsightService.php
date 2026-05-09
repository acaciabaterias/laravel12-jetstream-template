<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\FaturaSaaS;
use App\Models\InsightRiscoComercial;
use App\Models\SnapshotAnalyticsComercial;
use App\Support\Billing\CommercialAnalyticsRiskType;
use App\Support\Billing\PaymentReturnProcessingStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CommercialAnalyticsRiskInsightService
{
    public function rebuildForSnapshot(SnapshotAnalyticsComercial $snapshotAnalyticsComercial): void
    {
        $snapshotAnalyticsComercial->riskInsights()->delete();

        $periodEnd = Carbon::parse($snapshotAnalyticsComercial->period_end)->endOfDay();

        $delinquentInvoices = FaturaSaaS::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDate('vencimento', '<', $periodEnd)
            ->get();

        if ($delinquentInvoices->isNotEmpty()) {
            InsightRiscoComercial::query()->create([
                'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                'risk_type' => CommercialAnalyticsRiskType::Delinquency->value,
                'severity' => $delinquentInvoices->count() >= 5 ? 'high' : 'medium',
                'total_accounts' => $delinquentInvoices->pluck('cliente_id')->unique()->count(),
                'total_exposure' => round((float) $delinquentInvoices->sum('valor'), 2),
                'description' => 'Contas SaaS com faturas vencidas e sem baixa automatica.',
                'metadata' => [
                    'invoice_ids' => $delinquentInvoices->pluck('id')->values()->all(),
                ],
            ]);
        }

        if (Schema::connection('central')->hasTable('casos_recuperacao_receita')) {
            $stalledRecovery = CasoRecuperacaoReceita::query()
                ->whereIn('status', [RevenueRecoveryCaseStatus::Open->value, RevenueRecoveryCaseStatus::Escalated->value])
                ->whereDate('last_action_at', '<=', $periodEnd->copy()->subDays(5))
                ->get();

            if ($stalledRecovery->isNotEmpty()) {
                InsightRiscoComercial::query()->create([
                    'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                    'risk_type' => CommercialAnalyticsRiskType::RecoveryStall->value,
                    'severity' => $stalledRecovery->count() >= 3 ? 'high' : 'medium',
                    'total_accounts' => $stalledRecovery->pluck('cliente_id')->unique()->count(),
                    'total_exposure' => round((float) $stalledRecovery->loadMissing('fatura')->sum(fn ($case): float => (float) ($case->fatura?->valor ?? 0)), 2),
                    'description' => 'Casos de recuperacao sem acao recente dentro da janela esperada.',
                    'metadata' => [
                        'case_ids' => $stalledRecovery->pluck('id')->values()->all(),
                    ],
                ]);
            }
        }

        if (
            Schema::connection('central')->hasTable('cobrancas_saas_externas')
            && Schema::connection('central')->hasTable('retornos_pagamento_saas')
        ) {
            $paymentFailures = FaturaSaaS::query()
                ->whereHas('cobrancasExternas.retornos', function ($query): void {
                    $query->where('processing_status', PaymentReturnProcessingStatus::Failed->value);
                })
                ->get();

            if ($paymentFailures->isNotEmpty()) {
                InsightRiscoComercial::query()->create([
                    'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                    'risk_type' => CommercialAnalyticsRiskType::PaymentFailure->value,
                    'severity' => $paymentFailures->count() >= 3 ? 'high' : 'medium',
                    'total_accounts' => $paymentFailures->pluck('cliente_id')->unique()->count(),
                    'total_exposure' => round((float) $paymentFailures->sum('valor'), 2),
                    'description' => 'Falhas repetidas de retorno ou conciliacao no fluxo de cobranca.',
                    'metadata' => [
                        'invoice_ids' => $paymentFailures->pluck('id')->values()->all(),
                    ],
                ]);
            }
        }
    }
}
