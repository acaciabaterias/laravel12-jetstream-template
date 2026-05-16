<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RevenueRecoveryPolicyStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Inactive = 'inactive';
}
