<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum PaymentReconciliationStatus: string
{
    case Matched = 'matched';
    case PartiallyMatched = 'partially_matched';
    case Exception = 'exception';
    case Replayed = 'replayed';
    case Reversed = 'reversed';
}
