<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'ms_fiscal' => [
        'url' => env('MS_FISCAL_URL', 'http://localhost:8001'),
        'api_key' => env('MS_FISCAL_API_KEY'), // Chave central opcional, prioriza chave por filial
    ],

    'ms_bancario' => [
        'url' => env('MS_BANCARIO_URL', 'http://localhost:8002'),
        'api_key' => env('MS_BANCARIO_API_KEY'),
    ],

    'ms_whatsapp' => [
        'url' => env('MS_WHATSAPP_URL', 'http://localhost:8003'),
    ],

    'suporte' => [
        'whatsapp' => env('SUPPORT_WHATSAPP_NUMBER'),
        'compras_email' => env('COMPRAS_NOTIFICATION_TARGET', env('MAIL_FROM_ADDRESS', 'compras@example.com')),
    ],

    'platform' => [
        'maintenance_mode' => env('MAINTENANCE_MODE', false),
        'maintenance_allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('MAINTENANCE_ALLOWED_IPS', ''))))),
        'cors_allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '*'))))),
        'cors_allowed_methods' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_METHODS', 'GET,POST,PUT,PATCH,DELETE,OPTIONS'))))),
        'csp' => env('SECURITY_CSP', "default-src 'self'; frame-ancestors 'self'; base-uri 'self';"),
        'hsts' => env('SECURITY_HSTS', 'max-age=31536000; includeSubDomains'),
    ],

];
