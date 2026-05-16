<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptimizeRouteRequest;
use App\Http\Requests\RecalculateEtaRequest;
use App\Models\Parada;
use App\Models\Rota;
use App\Services\EtaService;
use App\Services\GeocodingService;
use App\Services\TspService;
use Illuminate\Http\JsonResponse;

class RoutingController extends Controller
{
    public function __construct(
        protected GeocodingService $geocoding,
        protected TspService $tsp,
        protected EtaService $eta
    ) {}

    public function otimizar(OptimizeRouteRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $nodes = [
            ['lat' => (float) $payload['base_lat'], 'lng' => (float) $payload['base_lng']],
        ];

        foreach ($payload['entregas'] as $entrega) {
            $coords = $this->geocoding->getCoordinates($entrega['endereco']);
            $nodes[] = [
                'lat' => $coords['lat'],
                'lng' => $coords['lng'],
            ];
        }

        $order = $this->tsp->optimize($nodes);
        $stopPayloads = [];
        $cursor = ['lat' => (float) $payload['base_lat'], 'lng' => (float) $payload['base_lng']];
        $distance = 0.0;
        $duration = 0;

        foreach (array_slice($order, 1) as $position => $nodeIndex) {
            $entrega = $payload['entregas'][$nodeIndex - 1];
            $coords = $nodes[$nodeIndex];
            $etaMinutes = $this->eta->calculateByTraffic($cursor, $coords);
            $duration += $etaMinutes;
            $distance += round(($etaMinutes / 60) * 40, 2);

            $stopPayloads[] = [
                'ordem' => $position + 1,
                'entrega_id' => $entrega['id'],
                'cliente_nome' => $entrega['cliente'],
                'endereco' => $entrega['endereco'],
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],
                'eta_chegada' => now()->addMinutes($duration),
                'status' => 'pendente',
            ];

            $cursor = $coords;
        }

        $rota = Rota::query()->create([
            'tenant_id_externo' => $payload['tenant_id_externo'],
            'base_operacional_id' => $payload['base_operacional_id'],
            'data_entrega' => $payload['data_entrega'],
            'status' => 'pendente',
            'paradas_json' => $stopPayloads,
            'distancia_total_km' => $distance,
            'duracao_estimada_min' => $duration,
            'otimizada_em' => now(),
        ]);

        foreach ($stopPayloads as $stopPayload) {
            Parada::query()->create(array_merge($stopPayload, ['rota_id' => $rota->id]));
        }

        return response()->json([
            'id' => $rota->id,
            'status' => 'otimizada',
            'paradas' => $stopPayloads,
            'distancia_total_km' => $rota->distancia_total_km,
            'duracao_estimada_min' => $rota->duracao_estimada_min,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(
            Rota::query()->with('paradas')->findOrFail($id)
        );
    }

    public function recalculateEta(RecalculateEtaRequest $request): JsonResponse
    {
        $rota = Rota::query()->with('paradas')->findOrFail($request->validated('rota_id'));
        $updated = $this->eta->recalculate(
            $rota->paradas->toArray(),
            [
                'lat' => (float) $request->validated('lat'),
                'lng' => (float) $request->validated('lng'),
            ]
        );

        return response()->json([
            'rota_id' => $rota->id,
            'paradas' => $updated,
        ]);
    }
}
