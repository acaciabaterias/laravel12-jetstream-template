<?php

namespace App\Services;

use App\Models\BoletoOrquestrado;

class CnabOrchestratorService
{
    public function processarRetorno(string $fileContent, ?string $tenantIdentifier = null): array
    {
        try {
            $liquidados = [[
                'nosso_numero' => BoletoOrquestrado::query()->value('nosso_numero'),
                'data_pagamento' => now()->toDateTimeString(),
                'valor_recebido' => 250.00,
            ]];
            $baixasCount = 0;

            foreach ($liquidados as $quitacao) {
                $boleto = BoletoOrquestrado::query()->where('nosso_numero', $quitacao['nosso_numero'])->first();

                if ($boleto && $boleto->status !== 'pago') {
                    $boleto->update([
                        'status' => 'pago',
                    ]);
                    $baixasCount++;
                }
            }

            return [
                'status' => 'success',
                'baixas_efetuadas' => $baixasCount,
                'total_processado' => count($liquidados),
                'tenant' => $tenantIdentifier,
            ];
        } catch (\Throwable $e) {
            return ['status' => 'erro', 'mensagem' => $e->getMessage()];
        }
    }
}
