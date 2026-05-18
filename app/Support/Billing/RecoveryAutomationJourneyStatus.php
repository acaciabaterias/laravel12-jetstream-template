<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RecoveryAutomationJourneyStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Paused = 'paused';
    case Completed = 'completed';
    case RolledBack = 'rolled_back';
}
