<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ConvertValeToPedidoJob;
use App\Models\PontoEntrega;
use App\Models\RecebimentoMovel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LogisticsSyncController extends Controller
{
    /**
     * Sincroniza dados coletados offline pelo App do Entregador.
     * Recebe um batch de atualizações de pontos e seus respectivos recebimentos.
     */
    public function sync(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.ponto_entrega_id' => 'required|exists:ponto_entregas,id',
            'updates.*.status' => 'required|string',
            'updates.*.peso_sucata_coletado' => 'nullable|numeric',
            'updates.*.recebimentos' => 'nullable|array',
            'updates.*.recebimentos.*.valor' => 'required|numeric',
            'updates.*.recebimentos.*.metodo' => 'required|string',
        ]);

        $results = [];

        foreach ($request->updates as $update) {
            try {
                DB::transaction(function () use ($update, &$results) {
                    $ponto = PontoEntrega::findOrFail($update['ponto_entrega_id']);
                    
                    // 1. Atualiza Status e Dados da Parada
                    $ponto->update([
                        'status' => $update['status'],
                        'peso_sucata_coletado' => $update['peso_sucata_coletado'] ?? $ponto->peso_sucata_coletado,
                        'checkout_at' => $update['status'] === 'concluido' ? now() : $ponto->checkout_at,
                    ]);

                    // 2. Registra Recebimentos se houver
                    if (isset($update['recebimentos']) && !empty($update['recebimentos'])) {
                        foreach ($update['recebimentos'] as $rec) {
                            RecebimentoMovel::create([
                                'ponto_entrega_id' => $ponto->id,
                                'filial_id' => $ponto->filial_id,
                                'valor' => $rec['valor'],
                                'metodo' => $rec['metodo'],
                                'status_sincronizado' => 'sincronizado',
                            ]);
                        }
                    }

                    $results[] = [
                        'ponto_entrega_id' => $ponto->id,
                        'status' => 'successo'
                    ];
                });
            } catch (\Exception $e) {
                Log::error("Erro no Sync Logístico: " . $e->getMessage(), ['update' => $update]);
                $results[] = [
                    'ponto_entrega_id' => $update['ponto_entrega_id'],
                    'status' => 'erro',
                    'message' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'message' => 'Sincronização processada',
            'results' => $results
        ]);
    }
}
