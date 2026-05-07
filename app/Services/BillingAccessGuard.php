<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class BillingAccessGuard
{
    /**
     * Determina se o acesso do cliente deve ser bloqueado por regras de billing.
     */
    public function shouldBlockClienteAccess(int $clienteId): bool
    {
        try {
            $isBlockedFlag = DB::connection('central')
                ->table('clientes')
                ->where('id', $clienteId)
                ->value('billing_blocked');

            if ((bool) $isBlockedFlag) {
                return true;
            }

            $hasPastDueSubscription = DB::connection('central')
                ->table('assinaturas')
                ->where('cliente_id', $clienteId)
                ->where('status', 'past_due')
                ->exists();

            if ($hasPastDueSubscription) {
                return true;
            }

            return DB::connection('central')
                ->table('faturas')
                ->where('cliente_id', $clienteId)
                ->where(function ($query): void {
                    $query
                        ->where('status', 'overdue')
                        ->orWhere(function ($subQuery): void {
                            $subQuery
                                ->where('status', 'pending')
                                ->whereDate('vencimento', '<', now()->toDateString());
                        });
                })
                ->exists();
        } catch (Throwable $exception) {
            Log::warning('Falha ao validar bloqueio por inadimplência.', [
                'cliente_id' => $clienteId,
                'error' => $exception->getMessage(),
            ]);

            return ! app()->environment('testing');
        }
    }
}
