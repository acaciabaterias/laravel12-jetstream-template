<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\SnapshotAnalyticsComercial;
use App\Support\Billing\CommercialAnalyticsSnapshotType;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CommercialAnalyticsSnapshotService
{
    public function __construct(
        private readonly CommercialAnalyticsCohortService $commercialAnalyticsCohortService,
        private readonly CommercialAnalyticsChannelService $commercialAnalyticsChannelService,
        private readonly CommercialAnalyticsDrilldownService $commercialAnalyticsDrilldownService,
        private readonly CommercialAnalyticsRiskInsightService $commercialAnalyticsRiskInsightService,
        private readonly PlatformCommercialAnalyticsEventPublisher $platformCommercialAnalyticsEventPublisher,
    ) {}

    public function rebuild(?Carbon $periodEnd = null, ?int $days = null): SnapshotAnalyticsComercial
    {
        $periodEnd ??= now();
        $days ??= (int) config('platform_commercial_analytics.snapshot.default_period_days', 30);
        $periodStart = $periodEnd->copy()->subDays($days)->startOfDay();

        $activeSubscriptions = AssinaturaPlataforma::query()
            ->with('plano')
            ->whereIn('status', ['active', 'past_due', 'paused'])
            ->whereDate('data_inicio', '<=', $periodEnd)
            ->get();

        $trackedSubscriptions = AssinaturaPlataforma::query()
            ->whereDate('data_inicio', '<=', $periodEnd)
            ->count();

        $churnCount = AssinaturaPlataforma::query()
            ->where('status', 'cancelled')
            ->whereBetween('data_termino', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->count();

        $delinquentInvoices = FaturaSaaS::query()
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDate('vencimento', '<', $periodEnd->toDateString())
            ->get();

        $recoveredCases = collect();

        if (Schema::connection('central')->hasTable('casos_recuperacao_receita')) {
            $recoveredCases = CasoRecuperacaoReceita::query()
                ->with('fatura')
                ->where('status', RevenueRecoveryCaseStatus::Recovered->value)
                ->whereBetween('updated_at', [$periodStart, $periodEnd->copy()->endOfDay()])
                ->get();
        }

        $snapshotAnalyticsComercial = SnapshotAnalyticsComercial::query()->create([
            'snapshot_type' => CommercialAnalyticsSnapshotType::Executive->value,
            'reference_date' => $periodEnd->toDateString(),
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
            'rebuild_status' => 'completed',
            'mrr_amount' => round((float) $activeSubscriptions->sum(fn (AssinaturaPlataforma $assinaturaPlataforma): float => (float) ($assinaturaPlataforma->plano?->preco_mensal ?? 0)), 2),
            'churn_count' => $churnCount,
            'churn_rate' => $trackedSubscriptions > 0 ? round($churnCount / $trackedSubscriptions, 4) : 0,
            'delinquent_count' => $delinquentInvoices->count(),
            'recovered_count' => $recoveredCases->count(),
            'recovered_amount' => round((float) $recoveredCases->sum(fn ($case): float => (float) ($case->fatura?->valor ?? 0)), 2),
            'blocked_count' => Cliente::query()->where('billing_blocked', true)->count(),
            'metadata' => [
                'days' => $days,
                'active_subscription_ids' => $activeSubscriptions->pluck('id')->values()->all(),
                'tracked_subscriptions' => $trackedSubscriptions,
                'delinquent_invoice_ids' => $delinquentInvoices->pluck('id')->values()->all(),
                'recovered_case_ids' => $recoveredCases->pluck('id')->values()->all(),
            ],
        ]);

        $this->commercialAnalyticsCohortService->rebuildForSnapshot($snapshotAnalyticsComercial);
        $this->commercialAnalyticsChannelService->rebuildForSnapshot($snapshotAnalyticsComercial);
        $this->commercialAnalyticsRiskInsightService->rebuildForSnapshot($snapshotAnalyticsComercial);
        $this->commercialAnalyticsDrilldownService->rebuildForSnapshot($snapshotAnalyticsComercial);

        $this->platformCommercialAnalyticsEventPublisher->publish(
            eventType: 'SNAPSHOT_ANALYTICS_ATUALIZADO',
            snapshotAnalyticsComercial: $snapshotAnalyticsComercial,
            payload: [
                'snapshot_id' => $snapshotAnalyticsComercial->id,
                'reference_date' => $snapshotAnalyticsComercial->reference_date?->toDateString(),
                'mrr_amount' => (float) $snapshotAnalyticsComercial->mrr_amount,
                'churn_count' => $snapshotAnalyticsComercial->churn_count,
                'delinquent_count' => $snapshotAnalyticsComercial->delinquent_count,
                'recovered_count' => $snapshotAnalyticsComercial->recovered_count,
                'blocked_count' => $snapshotAnalyticsComercial->blocked_count,
            ],
            consumers: ['platform', 'analytics'],
            schemaDefinition: [
                'snapshot_id' => 'integer',
                'reference_date' => 'string',
                'mrr_amount' => 'float',
                'churn_count' => 'integer',
                'delinquent_count' => 'integer',
                'recovered_count' => 'integer',
                'blocked_count' => 'integer',
            ],
        );

        if ($snapshotAnalyticsComercial->cohorts()->exists()) {
            $this->platformCommercialAnalyticsEventPublisher->publish(
                eventType: 'COORTE_COMERCIAL_ATUALIZADA',
                snapshotAnalyticsComercial: $snapshotAnalyticsComercial,
                payload: [
                    'snapshot_id' => $snapshotAnalyticsComercial->id,
                    'cohort_count' => $snapshotAnalyticsComercial->cohorts()->count(),
                    'labels' => $snapshotAnalyticsComercial->cohorts()->pluck('cohort_label')->values()->all(),
                ],
                consumers: ['platform'],
                schemaDefinition: [
                    'snapshot_id' => 'integer',
                    'cohort_count' => 'integer',
                    'labels' => 'array<string>',
                ],
            );
        }

        $riskInsight = $snapshotAnalyticsComercial->riskInsights()->orderByDesc('total_exposure')->first();

        if ($riskInsight !== null) {
            $this->platformCommercialAnalyticsEventPublisher->publish(
                eventType: 'INSIGHT_RISCO_IDENTIFICADO',
                snapshotAnalyticsComercial: $snapshotAnalyticsComercial,
                payload: [
                    'snapshot_id' => $snapshotAnalyticsComercial->id,
                    'risk_type' => $riskInsight->risk_type->value,
                    'severity' => $riskInsight->severity,
                    'total_accounts' => $riskInsight->total_accounts,
                    'total_exposure' => (float) $riskInsight->total_exposure,
                ],
                consumers: ['platform', 'ms-003'],
                schemaDefinition: [
                    'snapshot_id' => 'integer',
                    'risk_type' => 'string',
                    'severity' => 'string',
                    'total_accounts' => 'integer',
                    'total_exposure' => 'float',
                ],
            );
        }

        $degradedChannels = $snapshotAnalyticsComercial->channelMetrics()
            ->where('conversion_rate', '<', (float) config('platform_commercial_analytics.snapshot.degraded_conversion_rate', 0.5))
            ->where('total_cases', '>', 0)
            ->get();

        foreach ($degradedChannels as $degradedChannel) {
            $this->platformCommercialAnalyticsEventPublisher->publish(
                eventType: 'CANAL_PERFORMANCE_DEGRADADO',
                snapshotAnalyticsComercial: $snapshotAnalyticsComercial,
                payload: [
                    'snapshot_id' => $snapshotAnalyticsComercial->id,
                    'channel_type' => $degradedChannel->channel_type->value,
                    'channel_name' => $degradedChannel->channel_name,
                    'conversion_rate' => (float) $degradedChannel->conversion_rate,
                    'failed_cases' => $degradedChannel->failed_cases,
                ],
                consumers: ['platform'],
                schemaDefinition: [
                    'snapshot_id' => 'integer',
                    'channel_type' => 'string',
                    'channel_name' => 'string',
                    'conversion_rate' => 'float',
                    'failed_cases' => 'integer',
                ],
            );
        }

        return $snapshotAnalyticsComercial->fresh(['cohorts', 'channelMetrics', 'riskInsights', 'drilldowns']);
    }
}
