<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum CollectorHealthStatus: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Unavailable = 'unavailable';
}
