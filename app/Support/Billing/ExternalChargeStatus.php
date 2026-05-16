<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum ExternalChargeStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Pending = 'pending';
    case Paid = 'paid';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Failed = 'failed';
    case Refunded = 'refunded';
    case Chargeback = 'chargeback';
}
