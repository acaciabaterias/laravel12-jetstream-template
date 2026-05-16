<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\RecorteCoorteComercial;
use App\Models\SnapshotAnalyticsComercial;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CommercialAnalyticsCohortService
{
    public function rebuildForSnapshot(SnapshotAnalyticsComercial $snapshotAnalyticsComercial): void
    {
        $snapshotAnalyticsComercial->cohorts()->delete();

        $periodEnd = Carbon::parse($snapshotAnalyticsComercial->period_end)->endOfDay();
        $limit = (int) config('platform_commercial_analytics.snapshot.cohort_limit', 6);

        AssinaturaPlataforma::query()
            ->with(['plano'])
            ->whereDate('data_inicio', '<=', $periodEnd)
            ->orderByDesc('data_inicio')
            ->get()
            ->groupBy(fn (AssinaturaPlataforma $assinaturaPlataforma): string => Carbon::parse($assinaturaPlataforma->data_inicio)->format('Y-m'))
            ->take($limit)
            ->each(function ($subscriptions, string $label) use ($snapshotAnalyticsComercial): void {
                $subscriptionIds = $subscriptions->pluck('id');
                $activeSubscriptions = $subscriptions->whereIn('status', ['active', 'past_due', 'paused'])->count();
                $cancelledSubscriptions = $subscriptions->where('status', 'cancelled')->count();
                $delinquentSubscriptions = $subscriptions->where('status', 'past_due')->count();
                $recoveredSubscriptions = 0;

                if (Schema::connection('central')->hasTable('casos_recuperacao_receita')) {
                    $recoveredSubscriptions = CasoRecuperacaoReceita::query()
                        ->whereIn('assinatura_id', $subscriptionIds)
                        ->where('status', RevenueRecoveryCaseStatus::Recovered->value)
                        ->distinct('assinatura_id')
                        ->count('assinatura_id');
                }
                $mrrAmount = (float) $subscriptions
                    ->whereIn('status', ['active', 'past_due', 'paused'])
                    ->sum(fn (AssinaturaPlataforma $assinaturaPlataforma): float => (float) ($assinaturaPlataforma->plano?->preco_mensal ?? 0));

                RecorteCoorteComercial::query()->create([
                    'snapshot_analytics_comercial_id' => $snapshotAnalyticsComercial->id,
                    'cohort_label' => $label,
                    'cohort_start_date' => Carbon::createFromFormat('Y-m', $label)->startOfMonth()->toDateString(),
                    'cohort_end_date' => Carbon::createFromFormat('Y-m', $label)->endOfMonth()->toDateString(),
                    'active_subscriptions' => $activeSubscriptions,
                    'cancelled_subscriptions' => $cancelledSubscriptions,
                    'recovered_subscriptions' => $recoveredSubscriptions,
                    'delinquent_subscriptions' => $delinquentSubscriptions,
                    'mrr_amount' => round($mrrAmount, 2),
                    'metadata' => [
                        'subscription_ids' => $subscriptionIds->values()->all(),
                    ],
                ]);
            });
    }
}
