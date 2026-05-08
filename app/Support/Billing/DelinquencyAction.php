<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum DelinquencyAction: string
{
    case None = 'none';
    case MarkOverdue = 'mark_overdue';
    case StartGracePeriod = 'start_grace_period';
    case BlockSubscriber = 'block_subscriber';
    case ReactivateSubscriber = 'reactivate_subscriber';
}
