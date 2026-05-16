<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum PaymentExceptionStatus: string
{
    case Open = 'open';
    case Investigating = 'investigating';
    case Resolved = 'resolved';
    case Dismissed = 'dismissed';
}
