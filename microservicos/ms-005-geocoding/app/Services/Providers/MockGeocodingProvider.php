<?php

namespace App\Services\Providers;

use App\Contracts\GeocodingProvider;

class MockGeocodingProvider implements GeocodingProvider
{
    public function geocode(string $address): array
    {
        $hash = crc32($address);

        return [
            'lat' => -23.55 + (($hash % 100) / 1000),
            'lng' => -46.63 + (($hash % 100) / 1000),
            'confidence' => 'high',
            'provider' => 'mock',
        ];
    }

    public function reverseGeocode(float $lat, float $lng): string
    {
        return sprintf('Lat %.4f, Lng %.4f', $lat, $lng);
    }
}
