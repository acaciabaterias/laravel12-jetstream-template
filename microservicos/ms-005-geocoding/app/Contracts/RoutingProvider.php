<?php

namespace App\Contracts;

interface RoutingProvider
{
    /**
     * @param  array  $coordinates  Lista de {lat, lng} na ordem sugerida
     * @return array{distance: float, duration: float, polyline: string}
     */
    public function calculateRoute(array $coordinates): array;

    public function calculateEta(array $start, array $end): int;
}
