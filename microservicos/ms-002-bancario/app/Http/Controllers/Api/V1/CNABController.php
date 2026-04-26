<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GerarRemessaRequest;
use App\Http\Requests\ProcessarRetornoCnabRequest;
use App\Models\Cobranca;
use App\Models\RemessaCNAB;
use App\Services\BankService;
use Illuminate\Http\JsonResponse;

class CNABController extends Controller
{
    public function __construct(
        protected BankService $bank
    ) {}

    public function gerarRemessa(GerarRemessaRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $cobrancas = Cobranca::query()
            ->whereIn('id', $payload['cobranca_ids'])
            ->get()
            ->toArray();

        $result = $this->bank->gerarRemessa($cobrancas);

        $remessa = RemessaCNAB::query()->create([
            'banco_id' => $payload['banco_id'],
            'arquivo_nome' => $result['arquivo_nome'],
            'arquivo_base64' => $result['arquivo_base64'],
            'tipo' => 'REM',
            'status' => $result['status'],
            'registros_total' => $result['registros_total'],
            'registros_ok' => $result['registros_ok'],
            'registros_erro' => $result['registros_erro'],
        ]);

        return response()->json([
            'id' => $remessa->id,
            'arquivo_nome' => $remessa->arquivo_nome,
            'arquivo_base64' => $remessa->arquivo_base64,
            'status' => $remessa->status,
        ], 201);
    }

    public function processarRetorno(ProcessarRetornoCnabRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $remessa = RemessaCNAB::query()->create([
            'banco_id' => $payload['banco_id'],
            'arquivo_nome' => $payload['arquivo_nome'] ?? ('retorno_'.now()->timestamp.'.ret'),
            'arquivo_base64' => $payload['arquivo_base64'],
            'tipo' => 'RET',
            'status' => 'processando',
        ]);

        $result = $this->bank->processarRetorno($payload['arquivo_base64']);

        $remessa->update([
            'status' => $result['status'],
            'registros_total' => $result['total_processado'] ?? 0,
            'registros_ok' => count($result['pagamentos'] ?? []),
            'registros_erro' => 0,
        ]);

        foreach ($result['pagamentos'] ?? [] as $pagamento) {
            Cobranca::query()
                ->where('nosso_numero', $pagamento['nosso_numero'] ?? null)
                ->update([
                    'status' => $pagamento['status'] ?? 'pago',
                    'pago_em' => now(),
                    'pago_valor' => $pagamento['valor'] ?? null,
                ]);
        }

        return response()->json($result);
    }
}
