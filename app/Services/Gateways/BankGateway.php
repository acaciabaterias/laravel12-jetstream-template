<?php

namespace App\Services\Gateways;

use App\Models\FilaContingencia;
use App\Models\Filial;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BankGateway
{
    public function emitirBoleto(Filial $filial, array $payload, string $uuid): array
    {
        $url = config('services.ms_bancario.url').'/api/v1/boleto/emitir';
        $apiKey = config('services.ms_bancario.api_key');

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
                return $this->entrarEmContingencia($filial, $payload, 'bancario', 'Servidor MS-Bancário retornou erro 5xx', $uuid);
            }

            return ['status' => 'erro', 'mensagem' => $response->json('message')];

        } catch (\Exception $e) {
            return $this->entrarEmContingencia($filial, $payload, 'bancario', $e->getMessage(), $uuid);
        }
    }

    protected function entrarEmContingencia(Filial $filial, array $payload, string $tipo, string $motivo, string $uuid): array
    {
        Log::warning("Entrando em contingência {$tipo} para filial {$filial->id}. Motivo: {$motivo}");

        FilaContingencia::create([
            'tipo_integracao' => $tipo,
            'payload' => $payload,
            'status' => 'pendente',
            'proxima_tentativa' => now()->addMinutes(1),
            'idempotency_key' => $uuid ?: (string) Str::uuid(),
            'ultimo_erro' => $motivo,
        ]);

        return [
            'status' => 'contingencia',
            'mensagem' => 'Comunicação bancária falhou. Boleto enviado para fila de contingência.',
        ];
    }
}
