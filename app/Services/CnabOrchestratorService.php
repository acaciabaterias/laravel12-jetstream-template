<?php

namespace App\Services;

use App\Models\Boleto;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CnabOrchestratorService
{
    public function processarRetorno(string $fileContent, int $filialId): array
    {
        $url = config('services.ms_bancario.url') . '/api/v1/cnab/processar';
        
        try {
            // ERP atua como ponte passiva, enviando o conteúdo bruto em base64
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->post($url, [
                'filial_id' => $filialId,
                'arquivo_base64' => base64_encode($fileContent)
            ]);

            if ($response->failed()) {
                throw new \Exception("MS-Bancário falhou ao processar CNAB: " . $response->body());
            }

            $liquidados = $response->json('liquidados', []);
            $baixasCount = 0;

            foreach ($liquidados as $quitacao) {
                // Atualiza o boleto local baseado no Nosso Número retornado pelo MS
                $boleto = Boleto::where('nosso_numero', $quitacao['nosso_numero'])->first();
                
                if ($boleto && $boleto->status !== 'pago') {
                    $boleto->update([
                        'status' => 'pago',
                        'data_pagamento' => $quitacao['data_pagamento'],
                        'valor_pago' => $quitacao['valor_recebido']
                    ]);
                    $baixasCount++;
                }
            }

            return [
                'status' => 'success',
                'baixas_efetuadas' => $baixasCount,
                'total_processado' => count($liquidados)
            ];

        } catch (\Exception $e) {
            Log::error("Erro na Orquestração CNAB: " . $e->getMessage());
            return ['status' => 'erro', 'mensagem' => $e->getMessage()];
        }
    }
}
