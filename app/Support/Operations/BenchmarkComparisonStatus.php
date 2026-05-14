<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum BenchmarkComparisonStatus: string
{
    case Baseline = 'baseline';
    case Improved = 'improved';
    case Stable = 'stable';
    case Regressed = 'regressed';
}
