<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\ExecutiveAnalyticsSnapshot;
use App\Models\FaturaSaaS;
use App\Models\RetornoPagamentoSaaS;
use App\Models\SnapshotAnalyticsComercial;
use App\Support\Billing\ExecutiveAnalyticsSnapshotStatus;
use App\Support\Billing\ExecutiveReportingFilterNormalizer;
use App\Support\Billing\PaymentReturnProcessingStatus;
use App\Support\Billing\PlatformSubscriptionStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\SaasInvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ExecutiveAnalyticsSnapshotService
{
    public function __construct(
        private readonly CommercialAnalyticsSnapshotService $commercialAnalyticsSnapshotService,
        private readonly ExecutiveReportingFilterNormalizer $executiveReportingFilterNormalizer,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}
     */
    public function normalizeFilters(array $filters = []): array
    {
        return $this->executiveReportingFilterNormalizer->normalize($filters);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function latestOrCapture(array $filters = [], ?int $generatedBy = null): ExecutiveAnalyticsSnapshot
    {
        $normalized = $this->normalizeFilters($filters);
        $filterHash = $this->filterHash($normalized);

        return ExecutiveAnalyticsSnapshot::query()
            ->latest('id')
            ->where('filter_hash', $filterHash)
            ->where('snapshot_status', ExecutiveAnalyticsSnapshotStatus::Ready->value)
            ->first() ?? $this->capture($normalized, $generatedBy);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function capture(array $filters = [], ?int $generatedBy = null): ExecutiveAnalyticsSnapshot
    {
        $normalized = $this->normalizeFilters($filters);
        $sourceSnapshot = SnapshotAnalyticsComercial::query()
            ->latest('reference_date')
            ->first() ?? $this->commercialAnalyticsSnapshotService->rebuild(days: $normalized['days']);

        $subscriptions = $this->subscriptions($normalized);
        $invoices = $this->invoices($normalized);
        $recoveryCases = $this->recoveryCases($normalized);
        $paymentReturns = $this->paymentReturns($normalized);

        return ExecutiveAnalyticsSnapshot::query()->create([
            'snapshot_key' => config('executive_reporting.default_report_slug', 'executive-overview'),
            'source_snapshot_analytics_comercial_id' => $sourceSnapshot->id,
            'reference_date' => $normalized['period_end'],
            'period_start' => $normalized['period_start'],
            'period_end' => $normalized['period_end'],
            'filter_hash' => $this->filterHash($normalized),
            'filter_payload' => $normalized,
            'kpi_payload' => $this->buildKpiPayload($normalized, $subscriptions, $invoices, $recoveryCases, $paymentReturns),
            'drilldown_payload' => $this->buildDrilldownPayload($subscriptions, $invoices, $recoveryCases),
            'snapshot_status' => ExecutiveAnalyticsSnapshotStatus::Ready->value,
            'generated_by' => $generatedBy,
        ]);
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     * @return Collection<int, AssinaturaPlataforma>
     */
    private function subscriptions(array $filters): Collection
    {
        return $this->applySubscriptionFilters(
            AssinaturaPlataforma::query()->with(['cliente', 'plano', 'faturas']),
            $filters
        )->get();
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     * @return Collection<int, FaturaSaaS>
     */
    private function invoices(array $filters): Collection
    {
        return $this->applyInvoiceFilters(
            FaturaSaaS::query()->with(['cliente', 'assinatura.plano']),
            $filters
        )->get();
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     * @return Collection<int, CasoRecuperacaoReceita>
     */
    private function recoveryCases(array $filters): Collection
    {
        return $this->applyRecoveryFilters(
            CasoRecuperacaoReceita::query()->with(['cliente', 'assinatura.plano', 'fatura']),
            $filters
        )->get();
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     * @return Collection<int, RetornoPagamentoSaaS>
     */
    private function paymentReturns(array $filters): Collection
    {
        $query = RetornoPagamentoSaaS::query()
            ->with('cobranca.fatura.assinatura.plano')
            ->whereBetween('received_at', [$filters['period_start'].' 00:00:00', $filters['period_end'].' 23:59:59']);

        if ($filters['channel'] !== 'all') {
            $query->whereHas('cobranca.fatura', function (Builder $builder) use ($filters): void {
                $builder->where('billing_channel', $filters['channel']);
            });
        }

        if ($filters['plan'] !== 'all') {
            $query->whereHas('cobranca.fatura.assinatura.plano', function (Builder $builder) use ($filters): void {
                $builder->where('slug', $filters['plan']);
            });
        }

        return $query->get();
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     */
    private function applySubscriptionFilters(Builder $query, array $filters): Builder
    {
        $query->whereDate('data_inicio', '<=', $filters['period_end']);

        if ($filters['plan'] !== 'all') {
            $query->whereHas('plano', fn (Builder $builder): Builder => $builder->where('slug', $filters['plan']));
        }

        if ($filters['portfolio'] !== 'all') {
            $query->where('status', $filters['portfolio']);
        }

        if ($filters['channel'] !== 'all') {
            $query->whereHas('faturas', function (Builder $builder) use ($filters): void {
                $builder->where('billing_channel', $filters['channel']);
            });
        }

        if ($filters['recovery_status'] !== 'all') {
            $query->whereHas('cliente.casosRecuperacaoReceita', function (Builder $builder) use ($filters): void {
                $builder->where('status', $filters['recovery_status']);
            });
        }

        return $query;
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     */
    private function applyInvoiceFilters(Builder $query, array $filters): Builder
    {
        $query->whereBetween('vencimento', [$filters['period_start'], $filters['period_end']]);

        if ($filters['plan'] !== 'all') {
            $query->whereHas('assinatura.plano', fn (Builder $builder): Builder => $builder->where('slug', $filters['plan']));
        }

        if ($filters['channel'] !== 'all') {
            $query->where('billing_channel', $filters['channel']);
        }

        if ($filters['portfolio'] !== 'all') {
            $query->whereHas('assinatura', fn (Builder $builder): Builder => $builder->where('status', $filters['portfolio']));
        }

        if ($filters['recovery_status'] !== 'all') {
            $query->whereHas('cliente.casosRecuperacaoReceita', function (Builder $builder) use ($filters): void {
                $builder->where('status', $filters['recovery_status']);
            });
        }

        return $query;
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     */
    private function applyRecoveryFilters(Builder $query, array $filters): Builder
    {
        $query->whereBetween('opened_at', [$filters['period_start'].' 00:00:00', $filters['period_end'].' 23:59:59']);

        if ($filters['plan'] !== 'all') {
            $query->whereHas('assinatura.plano', fn (Builder $builder): Builder => $builder->where('slug', $filters['plan']));
        }

        if ($filters['channel'] !== 'all') {
            $query->whereHas('fatura', fn (Builder $builder): Builder => $builder->where('billing_channel', $filters['channel']));
        }

        if ($filters['portfolio'] !== 'all') {
            $query->whereHas('assinatura', fn (Builder $builder): Builder => $builder->where('status', $filters['portfolio']));
        }

        if ($filters['recovery_status'] !== 'all') {
            $query->where('status', $filters['recovery_status']);
        }

        return $query;
    }

    /**
     * @param  array{days:int,period_start:string,period_end:string,plan:string,channel:string,portfolio:string,recovery_status:string}  $filters
     * @param  Collection<int, AssinaturaPlataforma>  $subscriptions
     * @param  Collection<int, FaturaSaaS>  $invoices
     * @param  Collection<int, CasoRecuperacaoReceita>  $recoveryCases
     * @param  Collection<int, RetornoPagamentoSaaS>  $paymentReturns
     * @return array<string, int|float|string|array<string, string>>
     */
    private function buildKpiPayload(
        array $filters,
        Collection $subscriptions,
        Collection $invoices,
        Collection $recoveryCases,
        Collection $paymentReturns,
    ): array {
        $mrr = $subscriptions
            ->filter(fn (AssinaturaPlataforma $subscription): bool => in_array($subscription->status, [
                PlatformSubscriptionStatus::Active->value,
                PlatformSubscriptionStatus::GracePeriod->value,
                PlatformSubscriptionStatus::Blocked->value,
            ], true))
            ->sum(fn (AssinaturaPlataforma $subscription): float => (float) ($subscription->plano?->preco_mensal ?? 0));

        $overdueInvoices = $invoices->filter(function (FaturaSaaS $invoice): bool {
            return in_array($invoice->status, [SaasInvoiceStatus::Pending->value, SaasInvoiceStatus::Overdue->value], true);
        });

        $recoveredAmount = $invoices
            ->filter(fn (FaturaSaaS $invoice): bool => $invoice->status === SaasInvoiceStatus::Paid->value)
            ->sum(fn (FaturaSaaS $invoice): float => (float) ($invoice->valor_pago ?: $invoice->valor));

        $paymentFailures = $paymentReturns->filter(function (RetornoPagamentoSaaS $paymentReturn): bool {
            return $paymentReturn->processing_status === PaymentReturnProcessingStatus::Failed;
        })->count();

        $openRecoveryCases = $recoveryCases->filter(function (CasoRecuperacaoReceita $recoveryCase): bool {
            return in_array($recoveryCase->status, [
                RevenueRecoveryCaseStatus::Open,
                RevenueRecoveryCaseStatus::Paused,
                RevenueRecoveryCaseStatus::Escalated,
            ], true);
        })->count();

        return [
            'reference_date' => $filters['period_end'],
            'period_start' => $filters['period_start'],
            'period_end' => $filters['period_end'],
            'filters' => [
                'plan' => $filters['plan'],
                'channel' => $filters['channel'],
                'portfolio' => $filters['portfolio'],
                'recovery_status' => $filters['recovery_status'],
            ],
            'active_subscriptions' => $subscriptions->where('status', PlatformSubscriptionStatus::Active->value)->count(),
            'blocked_subscriptions' => $subscriptions->where('status', PlatformSubscriptionStatus::Blocked->value)->count(),
            'mrr' => round((float) $mrr, 2),
            'overdue_invoices' => $overdueInvoices->count(),
            'overdue_exposure' => round((float) $overdueInvoices->sum('valor'), 2),
            'recovered_amount' => round((float) $recoveredAmount, 2),
            'payment_failures' => $paymentFailures,
            'open_recovery_cases' => $openRecoveryCases,
            'at_risk_accounts' => $subscriptions->where('status', PlatformSubscriptionStatus::Blocked->value)->count() + $overdueInvoices->count(),
        ];
    }

    /**
     * @param  Collection<int, AssinaturaPlataforma>  $subscriptions
     * @param  Collection<int, FaturaSaaS>  $invoices
     * @param  Collection<int, CasoRecuperacaoReceita>  $recoveryCases
     * @return array<string, array<int, array<string, int|float|string>>>
     */
    private function buildDrilldownPayload(Collection $subscriptions, Collection $invoices, Collection $recoveryCases): array
    {
        $plans = $subscriptions
            ->groupBy(fn (AssinaturaPlataforma $subscription): string => (string) ($subscription->plano?->slug ?? 'sem-plano'))
            ->map(function (Collection $group, string $slug): array {
                return [
                    'key' => $slug,
                    'label' => (string) ($group->first()?->plano?->nome ?? $slug),
                    'subscriptions' => $group->count(),
                    'mrr' => round((float) $group->sum(fn (AssinaturaPlataforma $subscription): float => (float) ($subscription->plano?->preco_mensal ?? 0)), 2),
                ];
            })
            ->values()
            ->all();

        $channels = $invoices
            ->groupBy(fn (FaturaSaaS $invoice): string => (string) ($invoice->billing_channel ?: 'manual'))
            ->map(fn (Collection $group, string $channel): array => [
                'key' => $channel,
                'label' => $channel,
                'invoices' => $group->count(),
                'amount' => round((float) $group->sum('valor'), 2),
            ])
            ->values()
            ->all();

        $portfolios = $subscriptions
            ->groupBy('status')
            ->map(fn (Collection $group, string $status): array => [
                'key' => $status,
                'label' => $status,
                'subscriptions' => $group->count(),
            ])
            ->values()
            ->all();

        $recoveryStatuses = $recoveryCases
            ->groupBy(fn (CasoRecuperacaoReceita $recoveryCase): string => $recoveryCase->status->value)
            ->map(fn (Collection $group, string $status): array => [
                'key' => $status,
                'label' => $status,
                'cases' => $group->count(),
                'exposure' => round((float) $group->sum(fn (CasoRecuperacaoReceita $recoveryCase): float => (float) ($recoveryCase->fatura?->valor ?? 0)), 2),
            ])
            ->values()
            ->all();

        return [
            'plans' => $plans,
            'channels' => $channels,
            'portfolios' => $portfolios,
            'recovery_statuses' => $recoveryStatuses,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function filterHash(array $filters): string
    {
        return sha1((string) json_encode($filters));
    }
}
