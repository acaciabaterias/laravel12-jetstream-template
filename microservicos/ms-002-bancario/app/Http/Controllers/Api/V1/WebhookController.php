<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebhookBankRequest;
use App\Models\Cobranca;
use App\Models\WebhookRecebido;
use App\Services\BankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected BankService $bank
    ) {}

    public function handle(WebhookBankRequest $request, string $banco): JsonResponse
    {
        $payload = $request->validated();
        $signature = $request->header('X-Bank-Signature');

        if (! $this->bank->validarWebhook($banco, $payload, $signature)) {
            return response()->json(['error' => 'Webhook signature invalid.'], 401);
        }

        Log::info("Webhook recebido de {$banco}", $payload);

        WebhookRecebido::query()->create([
            'banco_slug' => $banco,
            'payload_raw' => $payload,
            'evento' => $payload['evento'] ?? 'cobranca.atualizada',
            'processado' => false,
        ]);

        $txid = $payload['txid'] ?? null;
        $nossoNumero = $payload['nosso_numero'] ?? null;

        $cobranca = null;
        if ($txid) {
            $cobranca = Cobranca::query()->where('txid', $txid)->first();
        } elseif ($nossoNumero) {
            $cobranca = Cobranca::query()->where('nosso_numero', $nossoNumero)->first();
        }

        if ($cobranca) {
            $cobranca->update([
                'status' => 'pago',
                'pago_em' => now(),
                'pago_valor' => $payload['valor_pago'] ?? $cobranca->valor,
            ]);

            Log::info("Cobrança {$cobranca->id} baixada via Webhook.");
        }

        WebhookRecebido::query()
            ->latest('id')
            ->first()
            ?->update(['processado' => true]);

        return response()->json(['status' => 'received']);
    }
}
