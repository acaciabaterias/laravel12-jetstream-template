<?php

return [
    'tolerances' => [
        'throughput_regression_ratio' => (float) env('LOAD_OPTIMIZATION_THROUGHPUT_REGRESSION_RATIO', 0.90),
        'latency_regression_ratio' => (float) env('LOAD_OPTIMIZATION_LATENCY_REGRESSION_RATIO', 1.15),
        'error_rate_regression_delta' => (float) env('LOAD_OPTIMIZATION_ERROR_RATE_REGRESSION_DELTA', 0.01),
    ],
    'events' => [
        'publish_to_backbone' => env('LOAD_OPTIMIZATION_PUBLISH_EVENTS', true),
    ],
];
