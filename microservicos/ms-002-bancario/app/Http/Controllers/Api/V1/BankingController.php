<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CancelCobrancaRequest;
use App\Http\Requests\EmitirBoletoRequest;
use App\Http\Requests\EmitirPixRequest;
use App\Models\BancoPerfil;
use App\Models\Cobranca;
use App\Services\BankService;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class BankingController extends Controller
{
    public function __construct(
        protected BankService $bank
    ) {}

    public function emitirBoleto(EmitirBoletoRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $existing = Cobranca::query()
            ->where('idempotency_key', $payload['idempotency_key'])
            ->first();

        if ($existing) {
            return response()->json($this->formatResponse($existing), 200);
        }

        $cobranca = Cobranca::query()->create([
            'idempotency_key' => $payload['idempotency_key'],
            'erp_fatura_id' => $payload['erp_fatura_id'],
            'banco_id' => $payload['banco_id'],
            'tipo' => 'boleto',
            'valor' => $payload['valor'],
            'vencimento' => $payload['vencimento'],
            'status' => 'pendente',
        ]);

        try {
            $result = $this->bank->gerarBoleto($payload);

            $cobranca->update([
                'nosso_numero' => $result['nosso_numero'],
                'linha_digitavel' => $result['linha_digitavel'],
                'codigo_barras' => $result['codigo_barras'] ?? null,
                'pdf_url' => $result['pdf_url'] ?? null,
                'status' => $result['status'],
            ]);

            return response()->json($this->formatResponse($cobranca));
        } catch (RuntimeException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function emitirPix(EmitirPixRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $existing = Cobranca::query()
            ->where('idempotency_key', $payload['idempotency_key'])
            ->first();

        if ($existing) {
            return response()->json($this->formatResponse($existing), 200);
        }

        $cobranca = Cobranca::query()->create([
            'idempotency_key' => $payload['idempotency_key'],
            'erp_fatura_id' => $payload['erp_fatura_id'],
            'banco_id' => $payload['banco_id'],
            'tipo' => 'pix',
            'valor' => $payload['valor'],
            'vencimento' => $payload['vencimento'] ?? now()->toDateString(),
            'status' => 'pendente',
        ]);

        try {
            $result = $this->bank->gerarPix($payload);

            $cobranca->update([
                'txid' => $result['txid'],
                'qrcode_pix' => $result['qr_code_string'],
                'qr_code_imagem_base64' => $result['qr_code_imagem_base64'] ?? null,
                'link_pagamento' => $result['link_pagamento'] ?? null,
                'status' => $result['status'],
            ]);

            return response()->json($this->formatResponse($cobranca));
        } catch (RuntimeException $exception) {
            return response()->json(['error' => $exception->getMessage()], 500);
        }
    }

    public function consultar(string $id): JsonResponse
    {
        $cobranca = Cobranca::query()->findOrFail($id);
        $status = $this->bank->consultar($cobranca->toArray());

        if (($status['status'] ?? null) !== null) {
            $cobranca->update(['status' => $status['status']]);
        }

        return response()->json($this->formatResponse($cobranca->fresh()));
    }

    public function cancelar(CancelCobrancaRequest $request, string $id): JsonResponse
    {
        $cobranca = Cobranca::query()->findOrFail($id);
        $result = $this->bank->cancelar($cobranca->toArray());

        $cobranca->update([
            'status' => $result['status'] ?? 'cancelado',
        ]);

        return response()->json([
            'id' => $cobranca->id,
            'status' => $cobranca->status,
            'protocolo' => $result['protocolo'] ?? null,
        ]);
    }

    public function listarBancos(): JsonResponse
    {
        return response()->json([
            'data' => BancoPerfil::query()->orderBy('nome')->get(),
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json($this->bank->health());
    }

    protected function formatResponse(Cobranca $cobranca): array
    {
        return [
            'id' => $cobranca->id,
            'erp_fatura_id' => $cobranca->erp_fatura_id,
            'nosso_numero' => $cobranca->nosso_numero,
            'linha_digitavel' => $cobranca->linha_digitavel,
            'pdf_url' => $cobranca->pdf_url,
            'qrcode_pix' => $cobranca->qrcode_pix,
            'link_pagamento' => $cobranca->link_pagamento,
            'status' => $cobranca->status,
        ];
    }
}
