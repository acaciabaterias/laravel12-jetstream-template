<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContatoBlacklist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlacklistController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'numero_tel' => 'required|string|unique:contato_blacklist,numero_tel',
            'motivo' => 'nullable|string',
        ]);

        $blacklist = ContatoBlacklist::query()->create($payload);

        return response()->json($blacklist, 201);
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => ContatoBlacklist::query()->orderBy('numero_tel')->get(),
        ]);
    }

    public function destroy(string $numero): JsonResponse
    {
        ContatoBlacklist::query()->where('numero_tel', $numero)->delete();

        return response()->json(['status' => 'removed']);
    }
}
