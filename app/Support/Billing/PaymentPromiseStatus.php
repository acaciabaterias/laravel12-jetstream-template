<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum PaymentPromiseStatus: string
{
    case Open = 'open';
    case Honored = 'honored';
    case Broken = 'broken';
    case Cancelled = 'cancelled';
}
