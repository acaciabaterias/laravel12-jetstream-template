<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum CommercialAnalyticsRebuildStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
