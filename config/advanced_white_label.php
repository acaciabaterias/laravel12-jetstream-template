<?php

declare(strict_types=1);

return [
    'required_tokens' => [
        'primary',
        'secondary',
        'surface',
        'accent',
        'text',
    ],
    'validation' => [
        'minimum_contrast_ratio' => 4.5,
    ],
    'fallback' => [
        'title' => 'BateriaExpert',
        'template_name' => 'default',
        'show_platform_brand' => true,
    ],
    'events' => [
        'publish_to_backbone' => true,
    ],
];
