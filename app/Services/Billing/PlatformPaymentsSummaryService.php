<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CobrancaSaaSExterna;
use App\Models\ExcecaoConciliacaoSaaS;
use App\Support\Billing\ExternalChargeStatus;
use App\Support\Billing\PaymentExceptionStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlatformPaymentsSummaryService
{
    /**
     * @return array<string, int|float>
     */
    public function summarize(): array
    {
        $pendingCharges = CobrancaSaaSExterna::query()
            ->whereIn('status', [
                ExternalChargeStatus::Submitted->value,
                ExternalChargeStatus::Pending->value,
            ])
            ->count();

        $paidCharges = CobrancaSaaSExterna::query()
            ->where('status', ExternalChargeStatus::Paid->value)
            ->count();

        $openExceptions = ExcecaoConciliacaoSaaS::query()
            ->whereIn('status', [
                PaymentExceptionStatus::Open->value,
                PaymentExceptionStatus::Investigating->value,
            ])
            ->count();

        $chargebackCases = ExcecaoConciliacaoSaaS::query()
            ->where('exception_type', 'chargeback')
            ->count();

        $pendingExposure = (float) CobrancaSaaSExterna::query()
            ->whereIn('status', [
                ExternalChargeStatus::Submitted->value,
                ExternalChargeStatus::Pending->value,
            ])
            ->sum('valor_emitido');

        return [
            'pending_charges' => $pendingCharges,
            'paid_charges' => $paidCharges,
            'open_exceptions' => $openExceptions,
            'chargeback_cases' => $chargebackCases,
            'pending_exposure' => round($pendingExposure, 2),
        ];
    }

    /**
     * @param  array{search?:string,status?:string,channel?:string,exception?:string}  $filters
     */
    public function charges(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = CobrancaSaaSExterna::query()
            ->with(['fatura.cliente', 'gateway'])
            ->latest();

        if (($filters['search'] ?? '') !== '') {
            $search = (string) $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('external_reference', 'like', '%'.$search.'%')
                    ->orWhereHas('fatura.cliente', function (Builder $clienteQuery) use ($search): void {
                        $clienteQuery->where('razao_social', 'like', '%'.$search.'%')
                            ->orWhere('subdominio', 'like', '%'.$search.'%');
                    });
            });
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (($filters['channel'] ?? 'all') !== 'all') {
            $query->where('payment_channel', $filters['channel']);
        }

        if (($filters['exception'] ?? 'all') !== 'all') {
            $query->whereHas('fatura.excecoesConciliacao', function (Builder $builder) use ($filters): void {
                $builder->where('exception_type', $filters['exception']);
            });
        }

        return $query->paginate($perPage);
    }
}
