<?php

return [
    'dunning' => [
        'evaluation_interval_minutes' => (int) env('PLATFORM_REVENUE_RECOVERY_EVALUATION_MINUTES', 5),
        'default_channels' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_REVENUE_RECOVERY_CHANNELS', 'email,whatsapp'))))),
        'replay_window_hours' => (int) env('PLATFORM_REVENUE_RECOVERY_REPLAY_WINDOW_HOURS', 48),
    ],
    'escalation' => [
        'days_overdue' => (int) env('PLATFORM_REVENUE_RECOVERY_ESCALATION_DAYS', 7),
        'severity_threshold' => env('PLATFORM_REVENUE_RECOVERY_SEVERITY_THRESHOLD', 'high'),
        'allow_reengagement' => filter_var(env('PLATFORM_REVENUE_RECOVERY_ALLOW_REENGAGEMENT', true), FILTER_VALIDATE_BOOL),
    ],
    'events' => [
        'publish_to_backbone' => filter_var(env('PLATFORM_REVENUE_RECOVERY_PUBLISH_EVENTS', true), FILTER_VALIDATE_BOOL),
        'default_consumers' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_REVENUE_RECOVERY_EVENT_CONSUMERS', 'platform,analytics,ms-003'))))),
    ],
];
