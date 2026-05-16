<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum PaymentReturnProcessingStatus: string
{
    case Pending = 'pending';
    case Processed = 'processed';
    case Ignored = 'ignored';
    case Failed = 'failed';
}
