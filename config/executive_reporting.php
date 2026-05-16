<?php

declare(strict_types=1);

return [
    'storage_disk' => env('EXECUTIVE_REPORTING_STORAGE_DISK', 'local'),
    'storage_directory' => env('EXECUTIVE_REPORTING_STORAGE_DIRECTORY', 'executive-reports'),
    'default_report_slug' => 'executive-overview',
    'supported_formats' => ['excel', 'pdf'],
    'visible_sections' => ['summary', 'plans', 'channels', 'portfolios', 'recovery_statuses'],
    'events' => [
        'publish_to_backbone' => env('EXECUTIVE_REPORTING_PUBLISH_EVENTS', true),
    ],
];
