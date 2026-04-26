<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocalizationRequest;
use App\Models\LocalizacaoEntregador;
use Illuminate\Http\JsonResponse;

class LocalizationController extends Controller
{
    public function store(StoreLocalizationRequest $request): JsonResponse
    {
        $location = LocalizacaoEntregador::query()->create($request->validated());

        return response()->json($location, 201);
    }

    public function show(int $entregadorId): JsonResponse
    {
        return response()->json(
            LocalizacaoEntregador::query()
                ->where('entregador_id', $entregadorId)
                ->latest('timestamp')
                ->firstOrFail()
        );
    }
}
