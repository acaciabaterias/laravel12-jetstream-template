<?php

return [
    'snapshot' => [
        'default_limit' => 20,
        'default_window_minutes' => 60,
    ],
    'thresholds' => [
        'backlog_warning' => 5,
        'backlog_critical' => 20,
        'latency_warning_ms' => 1500,
        'latency_critical_ms' => 5000,
        'failure_rate_warning' => 0.05,
        'failure_rate_critical' => 0.15,
        'stale_analytics_hours' => 24,
    ],
    'events' => [
        'publish_to_backbone' => env('PRODUCTION_OBSERVABILITY_PUBLISH_EVENTS', true),
    ],
];
