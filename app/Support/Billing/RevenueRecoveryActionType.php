<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RevenueRecoveryActionType: string
{
    case AutomatedReminder = 'automated_reminder';
    case ManualFollowUp = 'manual_follow_up';
    case Escalation = 'escalation';
    case PromiseFollowUp = 'promise_follow_up';
    case Reengagement = 'reengagement';
    case Replay = 'replay';
}
