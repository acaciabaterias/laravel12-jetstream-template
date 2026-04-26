<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fabricante;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileSyncController extends Controller
{
    /**
     * Sincroniza dados base para o aplicativo mobile offline (Entregador).
     * O MultiTenantScope garante que apenas os dados da filial atual sejam retornados.
     */
    public function sync(Request $request): JsonResponse
    {
        $fabricantes = Fabricante::select('id', 'nome')->orderBy('nome')->get();

        $veiculos = Veiculo::with([
            'baterias' => function (BelongsToMany $query): void {
                $query->select('baterias.id', 'sku', 'marca', 'tecnologia', 'polo');
            },
        ])
            ->select('id', 'fabricante_id', 'modelo', 'motorizacao', 'ano_inicio', 'ano_fim')
            ->orderBy('modelo')
            ->get();

        return response()->json([
            'fabricantes' => $fabricantes,
            'veiculos' => $veiculos,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
