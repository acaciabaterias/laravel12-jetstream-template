<?php

return [
    'notification' => [
        'default_driver' => env('NOTIFICATION_DEFAULT_DRIVER', 'whatsapp'),
    ],

    'n8n' => [
        'url' => env('N8N_URL', 'http://localhost:5678'),
        'webhook' => env('N8N_WHATSAPP_WEBHOOK', 'http://localhost:5678/webhook/whatsapp-trigger'),
    ],

    'evolution' => [
        'url' => env('EVOLUTION_API_URL', 'http://localhost:8080'),
        'api_key' => env('EVOLUTION_API_KEY'),
    ],
];
