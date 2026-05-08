<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RevenueRecoveryCaseStatus: string
{
    case Open = 'open';
    case Paused = 'paused';
    case Escalated = 'escalated';
    case Recovered = 'recovered';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
