<?php

declare(strict_types=1);

return [
    'supported_locales' => [
        'pt_BR' => ['label' => 'Português (Brasil)', 'native' => 'Português'],
        'en' => ['label' => 'English', 'native' => 'English'],
        'es' => ['label' => 'Español', 'native' => 'Español'],
    ],

    'default_locale' => 'pt_BR',
    'fallback_locale' => 'en',

    'required_translation_groups' => [
        'auth' => [
            'Administrative login',
            'Enter with your platform administrator account to access the central panel.',
            'Keep me signed in',
            'Go to ERP login',
        ],
        'navigation' => [
            'Dashboard',
            'Central Management',
            'Branches',
            'Customers / Subscribers',
            'Management Platform',
            'Logout',
        ],
        'dashboard' => [
            'Central Control',
            'Platform Dashboard',
            'Monitor monthly billing, tenant base and subscription health in an executive panel.',
            'Manage Tenants',
        ],
        'localization' => [
            'Platform internationalization',
            'Preferred language',
            'Publish locale bundle',
            'Rollback locale publication',
        ],
    ],

    'events' => [
        'publish_to_backbone' => true,
        'default_consumers' => ['platform', 'observability'],
    ],
];
