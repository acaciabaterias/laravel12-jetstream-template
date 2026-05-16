<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ACBr Driver Configuration
    |--------------------------------------------------------------------------
    |
    | Supported: "mock", "real"
    |
    */
    'driver' => env('ACBR_DRIVER', 'mock'),

    'schemes_path' => env('ACBR_SCHEMES_PATH', storage_path('app/acbr/schemas')),

    'certificate' => [
        'path' => env('ACBR_CERT_PATH'),
        'password' => env('ACBR_CERT_PASSWORD'),
    ],

    'contingency' => [
        'max_attempts' => env('ACBR_CONTINGENCY_MAX_ATTEMPTS', 10),
        'retry_schedule_minutes' => [1, 5, 30, 120, 360],
    ],
];
