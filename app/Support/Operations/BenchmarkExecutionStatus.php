<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum BenchmarkExecutionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Incomplete = 'incomplete';
}
