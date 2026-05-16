<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum PlatformSubscriptionStatus: string
{
    case Draft = 'draft';
    case Trial = 'trial';
    case Active = 'active';
    case GracePeriod = 'grace_period';
    case Blocked = 'blocked';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case PastDue = 'past_due';
    case Paused = 'paused';
    case Suspended = 'suspended';
}
