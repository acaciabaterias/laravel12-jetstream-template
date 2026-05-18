<?php

namespace App\Services\Gateways;

use App\Models\FilaContingencia;
use App\Models\Filial;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FiscalGateway
{
    public function emitir(Filial $filial, array $payload): array
    {
        $url = config('services.ms_fiscal.url').'/api/v1/emissao';
        $apiKey = config('services.ms_fiscal.api_key');

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(5)->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            // Fallback para Contingência se o MS responder erro (5xx)
            if ($response->serverError()) {
                return $this->entrarEmContingencia($filial, $payload, 'fiscal', 'Servidor MS-Fiscal retornou erro 5xx');
            }

            return ['status' => 'erro', 'mensagem' => $response->json('message')];

        } catch (\Exception $e) {
            // Fallback para Contingência se houver Timeout ou DNS Error
            return $this->entrarEmContingencia($filial, $payload, 'fiscal', $e->getMessage());
        }
    }

    protected function entrarEmContingencia(Filial $filial, array $payload, string $tipo, string $motivo): array
    {
        Log::warning("Entrando em contingência {$tipo} para filial {$filial->id}. Motivo: {$motivo}");

        FilaContingencia::create([
            'tipo_integracao' => $tipo,
            'payload' => $payload,
            'status' => 'pendente',
            'proxima_tentativa' => now()->addMinutes(1),
            'idempotency_key' => (string) Str::uuid(),
            'ultimo_erro' => $motivo,
        ]);

        return [
            'status' => 'contingencia',
            'mensagem' => 'Comunicação externa falhou. Nota enviada para fila de contingência.',
        ];
    }
}
