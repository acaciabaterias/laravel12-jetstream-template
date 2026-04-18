<?php

namespace App\Services\Gateways;

use App\Models\FilaContingencia;
use App\Models\Filial;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BankGateway
{
    public function emitirBoleto(Filial $filial, array $payload, string $uuid): array
    {
        $url = config('services.ms_bancario.url') . '/api/v1/boleto/emitir';
        $apiKey = $filial->ms_bancario_api_key;

        try {
            $response = Http::withHeaders([
                'X-API-KEY' => $apiKey,
                'X-IDEMPOTENCY-KEY' => $uuid,
                'Accept' => 'application/json',
            ])->timeout(5)->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            if ($response->serverError()) {
                return $this->entrarEmContingencia($filial, $payload, 'bancario', 'Servidor MS-Bancário retornou erro 5xx');
            }

            return ['status' => 'erro', 'mensagem' => $response->json('message')];

        } catch (\Exception $e) {
            return $this->entrarEmContingencia($filial, $payload, 'bancario', $e->getMessage());
        }
    }

    protected function entrarEmContingencia(Filial $filial, array $payload, string $tipo, string $motivo): array
    {
        Log::warning("Entrando em contingência {$tipo} para filial {$filial->id}. Motivo: {$motivo}");

        FilaContingencia::create([
            'filial_id' => $filial->id,
            'tipo' => $tipo,
            'payload' => $payload,
            'status' => 'pendente',
            'proxima_tentativa' => now()->addMinutes(1),
        ]);

        return [
            'status' => 'contingencia',
            'mensagem' => 'Comunicação bancária falhou. Boleto enviado para fila de contingência.'
        ];
    }
}
