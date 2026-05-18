<?php

return [
    'guardrails' => [
        'max_dispatches_per_day' => (int) env('ADVANCED_RECOVERY_MAX_DISPATCHES_PER_DAY', 3),
        'cooldown_hours' => (int) env('ADVANCED_RECOVERY_COOLDOWN_HOURS', 24),
        'suppression_hours' => (int) env('ADVANCED_RECOVERY_SUPPRESSION_HOURS', 48),
    ],
    'fallback' => [
        'default_order' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADVANCED_RECOVERY_FALLBACK_ORDER', 'whatsapp,email,manual_follow_up'))))),
        'allow_replay_after_hours' => (int) env('ADVANCED_RECOVERY_REPLAY_WINDOW_HOURS', 24),
    ],
    'experiments' => [
        'default_control_ratio' => (float) env('ADVANCED_RECOVERY_DEFAULT_CONTROL_RATIO', 0.10),
        'min_sample_size' => (int) env('ADVANCED_RECOVERY_MIN_SAMPLE_SIZE', 50),
    ],
    'events' => [
        'publish_to_backbone' => filter_var(env('ADVANCED_RECOVERY_PUBLISH_EVENTS', true), FILTER_VALIDATE_BOOL),
        'default_consumers' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADVANCED_RECOVERY_EVENT_CONSUMERS', 'platform,recovery,analytics,ms-003'))))),
    ],
];
