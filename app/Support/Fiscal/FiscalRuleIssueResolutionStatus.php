<?php

declare(strict_types=1);

namespace App\Support\Fiscal;

enum FiscalRuleIssueResolutionStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case RolledBack = 'rolled_back';
}
