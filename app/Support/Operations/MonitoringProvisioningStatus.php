<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum MonitoringProvisioningStatus: string
{
    case Pending = 'pending';
    case Applied = 'applied';
    case Failed = 'failed';
    case RolledBack = 'rolled_back';
}
