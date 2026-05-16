<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum CommercialEventType: string
{
    case SubscriptionActivated = 'subscription_activated';
    case PlanChanged = 'plan_changed';
    case SubscriptionCancelled = 'subscription_cancelled';
    case InvoiceOverdue = 'invoice_overdue';
    case GraceStarted = 'grace_started';
    case SubscriberBlocked = 'subscriber_blocked';
    case SubscriberReactivated = 'subscriber_reactivated';
}
