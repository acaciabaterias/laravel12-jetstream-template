<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum CommercialAnalyticsChannelType: string
{
    case Billing = 'billing';
    case Recovery = 'recovery';
}
