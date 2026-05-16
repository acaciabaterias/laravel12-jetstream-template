<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum ExecutiveReportExportStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Reexecuted = 'reexecuted';
}
