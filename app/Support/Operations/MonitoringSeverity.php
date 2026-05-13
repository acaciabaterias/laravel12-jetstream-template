<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum MonitoringSeverity: string
{
    case Healthy = 'healthy';
    case Warning = 'warning';
    case Critical = 'critical';
}
