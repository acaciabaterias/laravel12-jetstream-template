<?php

return [
    'snapshot' => [
        'default_type' => 'executive',
        'default_period_days' => 30,
        'default_limit' => 25,
        'cohort_limit' => 6,
        'drilldown_limit' => 50,
        'degraded_conversion_rate' => 0.5,
    ],
    'events' => [
        'publish_to_backbone' => env('PLATFORM_COMMERCIAL_ANALYTICS_PUBLISH_EVENTS', true),
    ],
];
