<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class InternalWebhookController extends Controller
{
    #[OA\Post(
        path: '/internal/webhooks/fiscal/status',
        summary: 'Recebe atualizações de status fiscal de microserviços',
        tags: ['Internal Webhooks'],
        security: [['bearerAuth' => []]]
    )]
    #[OA\Parameter(
        name: 'X-Internal-Service-Key',
        in: 'header',
        required: true,
        description: 'Chave de autenticação para serviços internos',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Parameter(
        name: 'X-Internal-Signature',
        in: 'header',
        required: true,
        description: 'Assinatura HMAC-SHA256 do corpo da requisição',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'processed'),
                new OA\Property(property: 'invoice_id', type: 'string', example: '12345'),
            ],
            type: 'object'
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Webhook processado com sucesso',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'received'),
                new OA\Property(property: 'message', type: 'string', example: 'Fiscal webhook processed.'),
            ]
        )
    )]
    #[OA\Response(response: 401, description: 'Não autorizado - Chave interna inválida ou ausente')]
    #[OA\Response(response: 429, description: 'Limite de requisições excedido')]
    public function fiscalStatus(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'received',
            'message' => 'Fiscal webhook processed.',
        ]);
    }
}
