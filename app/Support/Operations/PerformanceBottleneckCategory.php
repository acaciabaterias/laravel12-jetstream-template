<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum PerformanceBottleneckCategory: string
{
    case Database = 'database';
    case Queue = 'queue';
    case ExternalEndpoint = 'external_endpoint';
    case Application = 'application';
}
