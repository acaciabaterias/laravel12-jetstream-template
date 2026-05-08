<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum GatewayOperationalStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Degraded = 'degraded';
}
