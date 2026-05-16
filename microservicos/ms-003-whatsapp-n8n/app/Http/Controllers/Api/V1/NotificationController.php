<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendNotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notifier
    ) {}

    public function enviar(SendNotificationRequest $request): JsonResponse
    {
        $payload = $request->validated();

        $result = $this->notifier->send(
            $payload['to'],
            $payload['message'],
            $payload['evento'],
            $payload
        );

        return response()->json($result);
    }

    public function historico(string $clienteId): JsonResponse
    {
        return response()->json([
            'data' => $this->notifier->history($clienteId),
        ]);
    }

    public function fila(): JsonResponse
    {
        return response()->json([
            'data' => $this->notifier->queue(),
        ]);
    }

    public function health(): JsonResponse
    {
        return response()->json($this->notifier->health());
    }
}
