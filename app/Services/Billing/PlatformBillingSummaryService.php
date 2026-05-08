<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\AssinaturaPlataforma;
use App\Models\FaturaSaaS;
use App\Support\Billing\PlatformSubscriptionStatus;
use App\Support\Billing\SaasInvoiceStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlatformBillingSummaryService
{
    /**
     * @return array<string, int|float>
     */
    public function summarize(): array
    {
        $activeSubscriptions = AssinaturaPlataforma::query()
            ->where('status', PlatformSubscriptionStatus::Active->value)
            ->count();

        $graceSubscriptions = AssinaturaPlataforma::query()
            ->where('status', PlatformSubscriptionStatus::GracePeriod->value)
            ->count();

        $blockedSubscriptions = AssinaturaPlataforma::query()
            ->where('status', PlatformSubscriptionStatus::Blocked->value)
            ->count();

        $reactivatedRecently = AssinaturaPlataforma::query()
            ->whereNotNull('reactivated_at')
            ->where('reactivated_at', '>=', now()->subDays(30))
            ->count();

        $overdueExposure = (float) FaturaSaaS::query()
            ->whereIn('status', [
                SaasInvoiceStatus::Overdue->value,
                SaasInvoiceStatus::Pending->value,
            ])
            ->where('vencimento', '<', now()->toDateString())
            ->sum('valor');

        $mrr = (float) AssinaturaPlataforma::query()
            ->whereIn('status', [
                PlatformSubscriptionStatus::Active->value,
                PlatformSubscriptionStatus::GracePeriod->value,
                PlatformSubscriptionStatus::Blocked->value,
            ])
            ->with('plano')
            ->get()
            ->sum(fn (AssinaturaPlataforma $assinaturaPlataforma): float => (float) ($assinaturaPlataforma->plano->preco_mensal ?? 0));

        return [
            'active_subscriptions' => $activeSubscriptions,
            'grace_subscriptions' => $graceSubscriptions,
            'blocked_subscriptions' => $blockedSubscriptions,
            'reactivated_recently' => $reactivatedRecently,
            'overdue_exposure' => round($overdueExposure, 2),
            'mrr' => round($mrr, 2),
        ];
    }

    /**
     * @param  array{search?:string, status?:string, plan?:string, risk?:string}  $filters
     */
    public function subscribers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = AssinaturaPlataforma::query()
            ->with(['cliente', 'plano'])
            ->latest();

        if (($filters['search'] ?? '') !== '') {
            $search = (string) $filters['search'];

            $query->whereHas('cliente', function (Builder $builder) use ($search): void {
                $builder->where('razao_social', 'like', '%'.$search.'%')
                    ->orWhere('cnpj', 'like', '%'.$search.'%')
                    ->orWhere('subdominio', 'like', '%'.$search.'%');
            });
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (($filters['plan'] ?? 'all') !== 'all') {
            $query->whereHas('plano', function (Builder $builder) use ($filters): void {
                $builder->where('slug', $filters['plan']);
            });
        }

        if (($filters['risk'] ?? 'all') !== 'all') {
            match ($filters['risk']) {
                'overdue' => $query->whereHas('faturas', function (Builder $builder): void {
                    $builder->whereIn('status', [SaasInvoiceStatus::Pending->value, SaasInvoiceStatus::Overdue->value])
                        ->where('vencimento', '<', now()->toDateString());
                }),
                'grace' => $query->where('status', PlatformSubscriptionStatus::GracePeriod->value),
                'blocked' => $query->where('status', PlatformSubscriptionStatus::Blocked->value),
                'reactivated' => $query->whereNotNull('reactivated_at')
                    ->where('reactivated_at', '>=', now()->subDays(30)),
                default => null,
            };
        }

        return $query->paginate($perPage);
    }
}
