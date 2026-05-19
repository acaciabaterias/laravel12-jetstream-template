<?php

declare(strict_types=1);

namespace App\Support\Platform;

enum PlatformLocaleMissingKeyResolutionStatus: string
{
    case Open = 'open';
    case RolledBack = 'rolled_back';
    case Accepted = 'accepted';
}
