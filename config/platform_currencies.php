<?php

declare(strict_types=1);

return [
    'supported_currencies' => [
        'BRL' => ['label' => 'Brazilian Real', 'symbol' => 'R$', 'decimal_scale' => 2],
        'USD' => ['label' => 'US Dollar', 'symbol' => '$', 'decimal_scale' => 2],
        'EUR' => ['label' => 'Euro', 'symbol' => 'EUR', 'decimal_scale' => 2],
    ],

    'base_currency' => 'BRL',
    'default_currency' => 'BRL',

    'required_conversion_groups' => [
        'billing' => ['BRL', 'USD', 'EUR'],
        'payments' => ['BRL', 'USD'],
        'recovery' => ['BRL', 'USD'],
        'analytics' => ['BRL', 'USD', 'EUR'],
        'reports' => ['BRL', 'USD', 'EUR'],
    ],

    'events' => [
        'publish_to_backbone' => true,
        'default_consumers' => ['platform', 'billing', 'analytics'],
    ],
];
