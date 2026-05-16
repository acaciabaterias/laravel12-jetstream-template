<?php

declare(strict_types=1);

namespace App\Services\Billing;

use App\Models\FaturaSaaS;
use App\Models\PoliticaRecuperacaoReceita;
use App\Support\Billing\RevenueRecoveryPolicyStatus;
use RuntimeException;

class RevenueRecoveryPolicyService
{
    public function resolveForInvoice(FaturaSaaS $faturaSaaS): PoliticaRecuperacaoReceita
    {
        $policy = PoliticaRecuperacaoReceita::query()
            ->where('status', RevenueRecoveryPolicyStatus::Active->value)
            ->orderBy('id')
            ->first();

        if ($policy === null) {
            throw new RuntimeException(sprintf(
                'Nenhuma politica ativa de recuperacao de receita encontrada para a fatura %d.',
                $faturaSaaS->id
            ));
        }

        return $policy;
    }

    /**
     * @return array{name: string, channel: string, delay_hours: int}
     */
    public function resolveInitialStage(PoliticaRecuperacaoReceita $policy): array
    {
        $stageDefinitions = collect((array) $policy->stage_definitions);
        $firstStage = $stageDefinitions->first();

        if (! is_array($firstStage) || ! isset($firstStage['name'], $firstStage['channel'])) {
            return [
                'name' => 'd1',
                'channel' => 'email',
                'delay_hours' => 0,
            ];
        }

        return [
            'name' => (string) $firstStage['name'],
            'channel' => (string) $firstStage['channel'],
            'delay_hours' => (int) ($firstStage['delay_hours'] ?? 0),
        ];
    }
}
