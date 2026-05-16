<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RevenueRecoveryActionStatus: string
{
    case Scheduled = 'scheduled';
    case Processing = 'processing';
    case Sent = 'sent';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Skipped = 'skipped';
}
