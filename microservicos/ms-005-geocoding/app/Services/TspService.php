<?php

namespace App\Services;

class TspService
{
    public function optimize(array $nodes): array
    {
        if (count($nodes) <= 2) {
            return array_keys($nodes);
        }

        $unvisited = array_keys($nodes);
        $current = array_shift($unvisited);
        $tour = [$current];

        while (! empty($unvisited)) {
            $next = null;
            $minDist = INF;

            foreach ($unvisited as $key => $candidate) {
                $dist = $this->haversineDistance($nodes[$current], $nodes[$candidate]);
                if ($dist < $minDist) {
                    $minDist = $dist;
                    $next = $candidate;
                    $nextIndex = $key;
                }
            }

            $tour[] = $next;
            unset($unvisited[$nextIndex]);
            $current = $next;
        }

        return $this->twoOpt($tour, $nodes);
    }

    private function twoOpt(array $tour, array $nodes): array
    {
        $size = count($tour);
        $improvement = true;

        while ($improvement) {
            $improvement = false;
            for ($i = 1; $i < $size - 2; $i++) {
                for ($j = $i + 1; $j < $size - 1; $j++) {
                    if ($this->shouldSwap($tour, $nodes, $i, $j)) {
                        $tour = $this->swap($tour, $i, $j);
                        $improvement = true;
                    }
                }
            }
        }

        return $tour;
    }

    private function shouldSwap(array $tour, array $nodes, int $i, int $j): bool
    {
        $p1 = $nodes[$tour[$i - 1]];
        $p2 = $nodes[$tour[$i]];
        $p3 = $nodes[$tour[$j]];
        $p4 = $nodes[$tour[$j + 1]];

        $currentDist = $this->haversineDistance($p1, $p2) + $this->haversineDistance($p3, $p4);
        $newDist = $this->haversineDistance($p1, $p3) + $this->haversineDistance($p2, $p4);

        return $newDist < $currentDist;
    }

    private function swap(array $tour, int $i, int $j): array
    {
        $part1 = array_slice($tour, 0, $i);
        $part2 = array_reverse(array_slice($tour, $i, $j - $i + 1));
        $part3 = array_slice($tour, $j + 1);

        return array_merge($part1, $part2, $part3);
    }

    private function haversineDistance(array $p1, array $p2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($p2['lat'] - $p1['lat']);
        $dLng = deg2rad($p2['lng'] - $p1['lng']);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($p1['lat'])) * cos(deg2rad($p2['lat'])) *
             sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
