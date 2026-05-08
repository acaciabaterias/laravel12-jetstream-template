<?php

return [
    'gateways' => [
        'default_driver' => env('PLATFORM_PAYMENTS_DEFAULT_DRIVER', 'asaas'),
        'timeout_seconds' => (int) env('PLATFORM_PAYMENTS_TIMEOUT_SECONDS', 30),
        'supported_channels' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_PAYMENTS_SUPPORTED_CHANNELS', 'boleto,pix'))))),
    ],
    'idempotency' => [
        'reissue_window_minutes' => (int) env('PLATFORM_PAYMENTS_REISSUE_WINDOW_MINUTES', 60),
        'webhook_replay_window_hours' => (int) env('PLATFORM_PAYMENTS_WEBHOOK_REPLAY_WINDOW_HOURS', 48),
    ],
    'events' => [
        'publish_to_backbone' => filter_var(env('PLATFORM_PAYMENTS_PUBLISH_EVENTS', true), FILTER_VALIDATE_BOOL),
        'default_consumers' => array_values(array_filter(array_map('trim', explode(',', (string) env('PLATFORM_PAYMENTS_EVENT_CONSUMERS', 'platform,analytics'))))),
    ],
];
