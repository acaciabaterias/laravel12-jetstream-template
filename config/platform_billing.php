<?php

return [
    'delinquency' => [
        'grace_period_days' => (int) env('PLATFORM_BILLING_GRACE_PERIOD_DAYS', 3),
        'block_after_days' => (int) env('PLATFORM_BILLING_BLOCK_AFTER_DAYS', 7),
        'reactivation_mode' => env('PLATFORM_BILLING_REACTIVATION_MODE', 'automatic'),
        'notification_profile' => [
            'channels' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_BILLING_NOTIFICATION_CHANNELS', 'email,whatsapp'))))),
            'send_grace_alert' => filter_var(env('PLATFORM_BILLING_NOTIFY_GRACE', true), FILTER_VALIDATE_BOOL),
            'send_block_alert' => filter_var(env('PLATFORM_BILLING_NOTIFY_BLOCK', true), FILTER_VALIDATE_BOOL),
        ],
    ],
    'events' => [
        'publish_to_backbone' => filter_var(env('PLATFORM_BILLING_PUBLISH_EVENTS', false), FILTER_VALIDATE_BOOL),
        'default_consumers' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_BILLING_EVENT_CONSUMERS', 'platform,ms-003'))))),
    ],
];
