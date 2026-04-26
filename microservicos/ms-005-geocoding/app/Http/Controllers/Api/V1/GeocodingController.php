<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CorrectGeocodeRequest;
use App\Http\Requests\GeocodeRequest;
use App\Models\EnderecoGeocodificado;
use App\Services\GeocodingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class GeocodingController extends Controller
{
    public function __construct(
        protected GeocodingService $geocoding
    ) {}

    public function geocode(GeocodeRequest $request): JsonResponse
    {
        return response()->json($this->geocoding->getCoordinates($request->validated('address')));
    }

    public function correct(CorrectGeocodeRequest $request): JsonResponse
    {
        return response()->json(
            $this->geocoding->correctCoordinates(
                $request->validated('address'),
                (float) $request->validated('lat'),
                (float) $request->validated('lng'),
            )
        );
    }

    public function invalidate(string $hash): JsonResponse
    {
        Redis::del("geo:{$hash}");
        EnderecoGeocodificado::query()->where('endereco_hash', $hash)->delete();

        return response()->json(['status' => 'invalidated']);
    }

    public function health(): JsonResponse
    {
        return response()->json([
            'service' => 'ms-005-geocoding',
            'status' => 'ok',
        ]);
    }
}
