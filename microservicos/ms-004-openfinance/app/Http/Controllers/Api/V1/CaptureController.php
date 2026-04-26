<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CaptureExtratoRequest;
use App\Models\Consentimento;
use App\Models\ExtratoCapturaLog;
use App\Models\TransacaoBancaria;
use App\Services\EncryptionService;
use App\Services\Providers\MockProvider;
use Exception;
use Illuminate\Http\JsonResponse;

class CaptureController extends Controller
{
    public function __construct(
        protected EncryptionService $encryption
    ) {}

    public function capturar(CaptureExtratoRequest $request, int $consentimentoId): JsonResponse
    {
        $consentimento = Consentimento::query()->findOrFail($consentimentoId);
        $startedAt = microtime(true);
        $accessToken = $this->encryption->decrypt($consentimento->access_token_encrypted);
        $provider = new MockProvider;

        try {
            $transactions = $provider->fetchTransactions($accessToken);
            $novasCount = 0;
            $duplicatasCount = 0;

            foreach ($transactions as $txData) {
                $hash = TransacaoBancaria::generateHash(array_merge($txData, ['consentimento_id' => $consentimentoId]));

                if (TransacaoBancaria::query()->where('deduplicacao_hash', $hash)->exists()) {
                    $duplicatasCount++;

                    continue;
                }

                if (($txData['valor'] ?? 0) == 0.0) {
                    continue;
                }

                TransacaoBancaria::query()->create(array_merge($txData, [
                    'consentimento_id' => $consentimentoId,
                    'deduplicacao_hash' => $hash,
                    'tx_id_original' => $txData['tx_id'],
                    'data_lancamento' => $txData['data'],
                    'data_valor' => $txData['data'],
                    'categoria' => $txData['categoria'] ?? null,
                    'conta_origem' => $txData['conta_origem'] ?? null,
                    'conta_destino' => $txData['conta_destino'] ?? null,
                ]));

                $novasCount++;
            }

            ExtratoCapturaLog::query()->create([
                'consentimento_id' => $consentimentoId,
                'status' => 'success',
                'total_transacoes' => $novasCount,
                'periodo_de' => $request->validated('periodo_de'),
                'periodo_ate' => now(),
                'duracao_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response()->json([
                'status' => 'success',
                'novas_transacoes' => $novasCount,
                'duplicatas_ignoradas' => $duplicatasCount,
            ]);
        } catch (Exception $e) {
            ExtratoCapturaLog::query()->create([
                'consentimento_id' => $consentimentoId,
                'status' => 'error',
                'erro_descricao' => $e->getMessage(),
                'duracao_ms' => (int) ((microtime(true) - $startedAt) * 1000),
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function transacoes(): JsonResponse
    {
        return response()->json([
            'data' => TransacaoBancaria::query()->latest('data_lancamento')->get(),
        ]);
    }

    public function logs(): JsonResponse
    {
        return response()->json([
            'data' => ExtratoCapturaLog::query()->latest('created_at')->get(),
        ]);
    }
}
