<?php

return [
    'driver' => env('BANKING_DRIVER', 'mock'),
    'webhook_secret' => env('BANKING_WEBHOOK_SECRET'),
    'polling_interval_minutes' => (int) env('BANKING_POLLING_INTERVAL_MINUTES', 15),
];
