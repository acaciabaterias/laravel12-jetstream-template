<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum ExecutiveAnalyticsSnapshotStatus: string
{
    case Ready = 'ready';
    case Incomplete = 'incomplete';
}
