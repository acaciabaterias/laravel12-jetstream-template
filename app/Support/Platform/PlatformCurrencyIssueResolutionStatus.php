<?php

declare(strict_types=1);

namespace App\Support\Platform;

enum PlatformCurrencyIssueResolutionStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case RolledBack = 'rolled_back';
}
