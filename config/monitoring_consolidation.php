<?php

return [
    'scrape' => [
        'latency_warning_ms' => (int) env('MONITORING_SCRAPE_LATENCY_WARNING_MS', 1500),
        'latency_critical_ms' => (int) env('MONITORING_SCRAPE_LATENCY_CRITICAL_MS', 5000),
        'minimum_sample_count' => (int) env('MONITORING_SCRAPE_MINIMUM_SAMPLE_COUNT', 1),
    ],
    'alerts' => [
        'default_metric' => env('MONITORING_ALERT_DEFAULT_METRIC', 'latency_ms'),
        'default_operator' => env('MONITORING_ALERT_DEFAULT_OPERATOR', 'gte'),
        'material_event_type' => env('MONITORING_ALERT_MATERIAL_EVENT_TYPE', 'MONITORAMENTO_DEGRADADO'),
    ],
    'provisioning' => [
        'validation_window_minutes' => (int) env('MONITORING_PROVISIONING_VALIDATION_WINDOW_MINUTES', 60),
        'default_environment' => env('MONITORING_PROVISIONING_DEFAULT_ENVIRONMENT', 'staging'),
    ],
    'events' => [
        'publish_to_backbone' => env('MONITORING_CONSOLIDATION_PUBLISH_EVENTS', true),
    ],
];
