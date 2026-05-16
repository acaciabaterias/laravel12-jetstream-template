<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum SaasInvoiceStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';
    case WrittenOff = 'written_off';
}
