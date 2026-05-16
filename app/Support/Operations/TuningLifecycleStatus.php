<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum TuningLifecycleStatus: string
{
    case Pending = 'pending';
    case Validated = 'validated';
    case Promoted = 'promoted';
    case RolledBack = 'rolled_back';
}
