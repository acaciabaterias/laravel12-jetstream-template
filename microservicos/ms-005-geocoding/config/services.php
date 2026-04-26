<?php

return [
    'geocoding' => [
        'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'use_real_traffic' => (bool) env('USE_REAL_TRAFFIC', false),
    ],
];
