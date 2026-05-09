<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\FaturaSaaS;
use App\Models\MetricaPerformanceCanal;
use App\Models\SnapshotAnalyticsComercial;
use App\Support\Billing\CommercialAnalyticsChannelType;
use App\Support\Billing\RevenueRecoveryActionStatus;
use Illuminate\Support\Facades\Schema;

class CommercialAnalyticsChannelService
{
    public function rebuildForSnapshot(SnapshotAnalyticsComercial $snapshotAnalyticsComercial): void
    {
        $snapshotAnalyticsComercial->channelMetrics()->delete();

        FaturaSaaS::query()
            ->whereBetween('vencimento', [$snapshotAnalyticsComercial->period_start, $snapshotAnalyticsComercial->period_end])
            ->get()
            ->groupBy(fn (FaturaSaaS $faturaSaaS): string => (string) ($faturaSaaS->billing_channel ?: 'manual'))
            ->each(function ($invoices, string $channel) use ($snapshotAnalyticsComercial): void {
                $successfulCases = $invoices->where('status', 'paid')->count();
                $failedCases = $invoices->whereIn('status', ['cancelled', 'refunded', 'overdue'])->count();
                $totalCases = $invoices->count();
                $conversionRate = $totalCases > 0 ? round($successfulCases / $totalCases, 4) : 0;
                $recoveredAmount = (float) $invoices->where('status', 'paid')->sum('valor_pago');

                MetricaPerformanceCanal::query()->create([
                    'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                    'channel_type' => CommercialAnalyticsChannelType::Billing->value,
                    'channel_name' => $channel,
                    'total_cases' => $totalCases,
                    'successful_cases' => $successfulCases,
                    'failed_cases' => $failedCases,
                    'recovered_amount' => round($recoveredAmount, 2),
                    'conversion_rate' => $conversionRate,
                    'metadata' => [
                        'source' => 'faturas',
                    ],
                ]);
            });

        if (Schema::connection('central')->hasTable('acoes_recuperacao_receita')) {
            AcaoRecuperacaoReceita::query()
                ->whereBetween('created_at', [$snapshotAnalyticsComercial->period_start, $snapshotAnalyticsComercial->period_end])
                ->get()
                ->groupBy('channel')
                ->each(function ($actions, string $channel) use ($snapshotAnalyticsComercial): void {
                    $successfulCases = $actions->filter(function ($action): bool {
                        return in_array($action->status, [
                            RevenueRecoveryActionStatus::Completed,
                            RevenueRecoveryActionStatus::Sent,
                        ], true);
                    })->count();
                    $failedCases = $actions->filter(function ($action): bool {
                        return in_array($action->status, [
                            RevenueRecoveryActionStatus::Failed,
                            RevenueRecoveryActionStatus::Cancelled,
                        ], true);
                    })->count();
                    $totalCases = $actions->count();

                    MetricaPerformanceCanal::query()->create([
                        'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                        'channel_type' => CommercialAnalyticsChannelType::Recovery->value,
                        'channel_name' => (string) $channel,
                        'total_cases' => $totalCases,
                        'successful_cases' => $successfulCases,
                        'failed_cases' => $failedCases,
                        'recovered_amount' => 0,
                        'conversion_rate' => $totalCases > 0 ? round($successfulCases / $totalCases, 4) : 0,
                        'metadata' => [
                            'source' => 'acoes_recuperacao_receita',
                        ],
                    ]);
                });
        }
    }
}
