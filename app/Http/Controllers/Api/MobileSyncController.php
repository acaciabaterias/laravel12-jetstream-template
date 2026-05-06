<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Fabricante;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MobileSyncController extends Controller
{
    /**
     * Sincroniza dados base para o aplicativo mobile offline (Entregador).
     * O MultiTenantScope garante que apenas os dados da filial atual sejam retornados.
     */
    public function sync(Request $request): JsonResponse
    {
        $cacheKey = sprintf(
            'mobile-sync:%s:%s',
            config('database.connections.tenant.host', 'default-tenant'),
            md5((string) $request->header('User-Agent', 'unknown'))
        );

        $payload = Cache::remember($cacheKey, 300, function (): array {
            $fabricantes = Fabricante::select('id', 'nome')->orderBy('nome')->get();

            $veiculos = Veiculo::with([
                'baterias' => function (BelongsToMany $query): void {
                    $query->select('baterias.id', 'sku', 'marca', 'tecnologia', 'polo');
                },
            ])
                ->select('id', 'fabricante_id', 'modelo', 'motorizacao', 'ano_inicio', 'ano_fim')
                ->orderBy('modelo')
                ->get();

            return [
                'fabricantes' => $fabricantes,
                'veiculos' => $veiculos,
            ];
        });

        return response()->json([
            'fabricantes' => $payload['fabricantes'],
            'veiculos' => $payload['veiculos'],
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
