<?php

namespace App\Services;

use App\Contracts\GeocodingProvider;
use App\Models\EnderecoGeocodificado;
use App\Services\Providers\MockGeocodingProvider;
use Illuminate\Support\Facades\Redis;
use Throwable;

class GeocodingService
{
    public function __construct(
        protected ?GeocodingProvider $primaryProvider = null
    ) {
        $this->primaryProvider ??= app(MockGeocodingProvider::class);
    }

    public function getCoordinates(string $address): array
    {
        $hash = hash('sha256', strtolower(trim($address)));

        if ($cached = Redis::get("geo:{$hash}")) {
            return json_decode($cached, true);
        }

        if ($dbResult = EnderecoGeocodificado::query()->where('endereco_hash', $hash)->first()) {
            $data = [
                'lat' => $dbResult->latitude,
                'lng' => $dbResult->longitude,
                'confidence' => $dbResult->confianca,
            ];

            Redis::setex("geo:{$hash}", 2592000, json_encode($data));

            return $data;
        }

        try {
            $result = $this->primaryProvider->geocode($address);

            EnderecoGeocodificado::query()->updateOrCreate(
                ['endereco_hash' => $hash],
                [
                    'logradouro' => $address,
                    'latitude' => $result['lat'],
                    'longitude' => $result['lng'],
                    'provider_usado' => $result['provider'] ?? 'mock',
                    'confianca' => $result['confidence'],
                    'expires_at' => now()->addDays(30),
                ]
            );

            Redis::setex("geo:{$hash}", 2592000, json_encode($result));

            return $result;
        } catch (Throwable $exception) {
            throw $exception;
        }
    }

    public function correctCoordinates(string $address, float $lat, float $lng): array
    {
        $hash = hash('sha256', strtolower(trim($address)));
        $data = [
            'lat' => $lat,
            'lng' => $lng,
            'confidence' => 'manual',
            'provider' => 'manual',
        ];

        EnderecoGeocodificado::query()->updateOrCreate(
            ['endereco_hash' => $hash],
            [
                'logradouro' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'provider_usado' => 'manual',
                'confianca' => 'manual',
                'ajustado_manualmente' => true,
                'expires_at' => null,
            ]
        );

        Redis::set("geo:{$hash}", json_encode($data));

        return $data;
    }
}
