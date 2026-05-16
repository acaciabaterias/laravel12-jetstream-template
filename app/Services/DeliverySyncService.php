<?php

namespace App\Services;

use App\Models\ContaSucataMovimentacao;
use App\Models\GeolocalizacaoEvento;
use App\Models\ItemVale;
use App\Models\PontoEntrega;
use App\Models\RecebimentoMovel;
use App\Models\SyncEvento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliverySyncService
{
    /**
     * Eventos com valor operacional para persistência.
     *
     * @var string[]
     */
    protected array $relevantGeoEventTypes = [
        'checkin',
        'checkout',
        'entrega_realizada',
        'ocorrencia',
    ];

    public function sync(array $payload): SyncEvento
    {
        return DB::transaction(function () use ($payload): SyncEvento {
            $payloadHash = sha1(json_encode($payload));

            $syncEvento = SyncEvento::query()->firstOrCreate(
                [
                    'payload_hash' => $payloadHash,
                ],
                [
                    'dispositivo_uuid' => $this->normalizeDeviceUuid((string) $payload['dispositivo_uuid']),
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
                if (! in_array((string) ($payload['tipo_evento'] ?? ''), $this->relevantGeoEventTypes, true)) {
                    $syncEvento->update([
                        'status' => 'processado',
                        'processed_at' => now(),
                    ]);

                    return $syncEvento->fresh();
                }

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
                $ponto = PontoEntrega::query()->with('vale')->findOrFail($payload['entidade_id']);

                $ponto->update([
                    'peso_sucata_coletado' => $payload['peso_sucata_coletado'],
                    'status' => $payload['status'] ?? 'em_andamento',
                    'observacao' => $payload['observacao'] ?? null,
                ]);

                $pesoColetadoKg = round((float) $payload['peso_sucata_coletado'], 2);
                $valorUnitario = $this->resolveSucataUnitValue($ponto->vale_id);
                $valorMovimentado = round($pesoColetadoKg * $valorUnitario, 2);
                $saldoAnterior = (float) (ContaSucataMovimentacao::query()->latest('id')->value('saldo_resultante') ?? 0.0);

                ContaSucataMovimentacao::query()->updateOrCreate(
                    [
                        'entidade_tipo' => PontoEntrega::class,
                        'entidade_id' => $ponto->id,
                        'origem' => 'logistica_sucata',
                    ],
                    [
                        'tipo_movimento' => 'credito',
                        'quantidade_kg' => $pesoColetadoKg,
                        'valor_unitario' => $valorUnitario,
                        'saldo_resultante' => round($saldoAnterior + $valorMovimentado, 2),
                    ],
                );
            }

            $syncEvento->update([
                'status' => 'processado',
                'processed_at' => now(),
            ]);

            return $syncEvento->fresh();
        });
    }

    protected function resolveSucataUnitValue(?int $valeId): float
    {
        if (! $valeId) {
            return 0.0;
        }

        $itens = ItemVale::query()
            ->with('bateria')
            ->where('vale_id', $valeId)
            ->get();

        if ($itens->isEmpty()) {
            return 0.0;
        }

        $weightedValue = $itens->sum(function (ItemVale $item): float {
            return (float) ($item->bateria?->valor_base_sucata_kg ?? 0) * (int) $item->quantidade;
        });
        $totalQuantity = max(1, (int) $itens->sum('quantidade'));

        return round($weightedValue / $totalQuantity, 2);
    }

    protected function normalizeDeviceUuid(string $deviceIdentifier): string
    {
        if (Str::isUuid($deviceIdentifier)) {
            return $deviceIdentifier;
        }

        $hash = md5($deviceIdentifier);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($hash, 0, 8),
            substr($hash, 8, 4),
            substr($hash, 12, 4),
            substr($hash, 16, 4),
            substr($hash, 20, 12),
        );
    }
}
