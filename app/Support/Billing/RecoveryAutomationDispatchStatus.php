<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RecoveryAutomationDispatchStatus: string
{
    case Scheduled = 'scheduled';
    case Dispatched = 'dispatched';
    case Failed = 'failed';
    case Suppressed = 'suppressed';
    case Cancelled = 'cancelled';
    case Replayed = 'replayed';
}
