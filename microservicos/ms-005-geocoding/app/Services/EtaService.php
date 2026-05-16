<?php

namespace App\Services;

class EtaService
{
    public function estimateByDistance(float $distanceKm, bool $isUrban = true): int
    {
        $speed = $isUrban ? 40 : 80;
        $hours = $distanceKm / $speed;

        return (int) round($hours * 60);
    }

    public function calculateByTraffic(array $start, array $end): int
    {
        return $this->estimateByDistance($this->haversine($start, $end));
    }

    public function recalculate(array $route, array $currentPosition): array
    {
        $updatedStops = [];
        $cursor = $currentPosition;
        $accumulated = 0;

        foreach ($route as $stop) {
            $eta = $this->calculateByTraffic($cursor, [
                'lat' => $stop['latitude'],
                'lng' => $stop['longitude'],
            ]);

            $accumulated += $eta;
            $updatedStops[] = array_merge($stop, [
                'eta_min' => $accumulated,
            ]);

            $cursor = [
                'lat' => $stop['latitude'],
                'lng' => $stop['longitude'],
            ];
        }

        return $updatedStops;
    }

    private function haversine(array $p1, array $p2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($p2['lat'] - $p1['lat']);
        $dLng = deg2rad($p2['lng'] - $p1['lng']);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($p1['lat'])) * cos(deg2rad($p2['lat'])) *
             sin($dLng / 2) * sin($dLng / 2);

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
