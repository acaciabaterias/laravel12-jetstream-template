<?php

namespace App\Services;

use App\Models\GeolocalizacaoEvento;
use App\Models\PontoEntrega;
use App\Models\RecebimentoMovel;
use App\Models\SyncEvento;
use Illuminate\Support\Facades\DB;

class DeliverySyncService
{
    public function sync(array $payload): SyncEvento
    {
        return DB::transaction(function () use ($payload): SyncEvento {
            $payloadHash = sha1(json_encode($payload));

            $syncEvento = SyncEvento::query()->firstOrCreate(
                [
                    'payload_hash' => $payloadHash,
                ],
                [
                    'dispositivo_uuid' => $payload['dispositivo_uuid'],
                    'entidade_tipo' => $payload['entidade_tipo'],
                    'entidade_id' => $payload['entidade_id'] ?? null,
                    'payload' => $payload,
                    'status' => 'pendente',
                ],
            );

            if ($syncEvento->status === 'processado') {
                return $syncEvento;
            }

            if ($payload['entidade_tipo'] === RecebimentoMovel::class) {
                RecebimentoMovel::query()->updateOrCreate(
                    [
                        'ponto_entrega_id' => $payload['ponto_entrega_id'],
                        'valor' => $payload['valor'],
                        'metodo_pagamento' => $payload['metodo_pagamento'],
                    ],
                    [
                        'status_sincronizado' => true,
                        'comprovante_path' => $payload['comprovante_path'] ?? null,
                    ],
                );
            }

            if ($payload['entidade_tipo'] === GeolocalizacaoEvento::class) {
                GeolocalizacaoEvento::query()->firstOrCreate(
                    [
                        'rota_entrega_id' => $payload['rota_entrega_id'] ?? null,
                        'ponto_entrega_id' => $payload['ponto_entrega_id'] ?? null,
                        'tipo_evento' => $payload['tipo_evento'],
                        'recorded_at' => $payload['recorded_at'],
                    ],
                    [
                        'latitude' => $payload['latitude'],
                        'longitude' => $payload['longitude'],
                    ],
                );
            }

            if ($payload['entidade_tipo'] === PontoEntrega::class && isset($payload['peso_sucata_coletado'])) {
                PontoEntrega::query()->findOrFail($payload['entidade_id'])->update([
                    'peso_sucata_coletado' => $payload['peso_sucata_coletado'],
                    'status' => $payload['status'] ?? 'em_andamento',
                    'observacao' => $payload['observacao'] ?? null,
                ]);
            }

            $syncEvento->update([
                'status' => 'processado',
                'processed_at' => now(),
            ]);

            return $syncEvento->fresh();
        });
    }
}
