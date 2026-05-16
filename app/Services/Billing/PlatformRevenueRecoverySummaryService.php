<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\CasoRecuperacaoReceita;
use App\Models\CompromissoPagamento;
use App\Support\Billing\PaymentPromiseStatus;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class PlatformRevenueRecoverySummaryService
{
    /**
     * @return array<string, int|float>
     */
    public function summarize(): array
    {
        $openCases = CasoRecuperacaoReceita::query()
            ->where('status', RevenueRecoveryCaseStatus::Open->value)
            ->count();

        $pausedCases = CasoRecuperacaoReceita::query()
            ->where('status', RevenueRecoveryCaseStatus::Paused->value)
            ->count();

        $escalatedCases = CasoRecuperacaoReceita::query()
            ->where('status', RevenueRecoveryCaseStatus::Escalated->value)
            ->count();

        $recoveredCases = CasoRecuperacaoReceita::query()
            ->where('status', RevenueRecoveryCaseStatus::Recovered->value)
            ->count();

        $brokenPromises = CompromissoPagamento::query()
            ->where('status', PaymentPromiseStatus::Broken->value)
            ->count();

        $promisedExposure = (float) CompromissoPagamento::query()
            ->where('status', PaymentPromiseStatus::Open->value)
            ->sum('promised_amount');

        return [
            'open_cases' => $openCases,
            'paused_cases' => $pausedCases,
            'escalated_cases' => $escalatedCases,
            'recovered_cases' => $recoveredCases,
            'broken_promises' => $brokenPromises,
            'promised_exposure' => round($promisedExposure, 2),
        ];
    }

    /**
     * @param  array{search?:string,status?:string,stage?:string,severity?:string,owner?:string}  $filters
     */
    public function cases(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = CasoRecuperacaoReceita::query()
            ->with(['cliente', 'fatura', 'owner'])
            ->latest();

        if (($filters['search'] ?? '') !== '') {
            $search = (string) $filters['search'];

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('current_stage', 'like', '%'.$search.'%')
                    ->orWhereHas('cliente', function (Builder $clienteQuery) use ($search): void {
                        $clienteQuery->where('razao_social', 'like', '%'.$search.'%')
                            ->orWhere('subdominio', 'like', '%'.$search.'%');
                    });
            });
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (($filters['stage'] ?? 'all') !== 'all') {
            $query->where('current_stage', $filters['stage']);
        }

        if (($filters['severity'] ?? 'all') !== 'all') {
            $query->where('severity', $filters['severity']);
        }

        if (($filters['owner'] ?? 'all') !== 'all') {
            $query->where('owner_user_id', (int) $filters['owner']);
        }

        return $query->paginate($perPage);
    }

    public function isEligibleForReengagement(CasoRecuperacaoReceita $casoRecuperacaoReceita): bool
    {
        if (! config('platform_revenue_recovery.escalation.allow_reengagement', true)) {
            return false;
        }

        if ($casoRecuperacaoReceita->status !== RevenueRecoveryCaseStatus::Recovered) {
            return false;
        }

        if ($casoRecuperacaoReceita->relationLoaded('compromissos')) {
            return ! $casoRecuperacaoReceita->compromissos
                ->contains(fn ($promise) => $promise->status === PaymentPromiseStatus::Open);
        }

        return ! $casoRecuperacaoReceita->compromissos()
            ->where('status', PaymentPromiseStatus::Open->value)
            ->exists();
    }
}
