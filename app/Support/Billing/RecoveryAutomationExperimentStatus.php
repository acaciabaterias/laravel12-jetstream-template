<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum RecoveryAutomationExperimentStatus: string
{
    case Draft = 'draft';
    case Running = 'running';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
