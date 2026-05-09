<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum OperationalIncidentStatus: string
{
    case Open = 'open';
    case Acknowledged = 'acknowledged';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
