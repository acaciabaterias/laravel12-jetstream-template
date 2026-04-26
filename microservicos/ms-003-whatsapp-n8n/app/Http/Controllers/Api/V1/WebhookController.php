<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContatoBlacklist;
use App\Models\WorkflowExecucao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handleEvolution(Request $request): JsonResponse
    {
        $payload = $request->all();
        $messageText = $payload['data']['message']['conversation'] ?? '';
        $from = $payload['data']['key']['remoteJid'] ?? '';
        $number = explode('@', $from)[0] ?? '';

        if (empty($number)) {
            return response()->json(['status' => 'ignored']);
        }

        $stopWords = ['PARAR', 'SAIR', 'STOP', 'CANCELAR'];
        if (in_array(strtoupper(trim($messageText)), $stopWords)) {
            ContatoBlacklist::query()->firstOrCreate(
                ['numero_tel' => $number],
                ['motivo' => 'Solicitado via WhatsApp: '.$messageText]
            );

            WorkflowExecucao::query()->create([
                'workflow_name' => 'wf-opt-out',
                'evento_trigger' => 'CLIENTE_OPT_OUT',
                'status' => 'success',
                'payload_entrada' => $payload,
                'mensagem_enviada' => $messageText,
                'canal' => 'whatsapp',
                'destinatario' => $number,
            ]);

            Log::info("Cliente {$number} solicitou opt-out e foi adicionado à Blacklist.");

            return response()->json(['status' => 'blacklisted']);
        }

        return response()->json(['status' => 'received']);
    }

    public function handleErp(Request $request, string $evento): JsonResponse
    {
        $payload = $request->all();

        WorkflowExecucao::query()->create([
            'workflow_name' => 'wf-erp-trigger',
            'evento_trigger' => $evento,
            'status' => 'received',
            'payload_entrada' => $payload,
            'mensagem_enviada' => $payload['message'] ?? null,
            'canal' => $payload['canal'] ?? 'whatsapp',
            'destinatario' => $payload['to'] ?? null,
        ]);

        return response()->json([
            'status' => 'queued',
            'evento' => $evento,
        ], 202);
    }
}
