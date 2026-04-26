<?php

namespace App\Contracts;

interface GeocodingProvider
{
    /**
     * @return array{lat: float, lng: float, confidence: string}
     */
    public function geocode(string $address): array;

    public function reverseGeocode(float $lat, float $lng): string;
}
