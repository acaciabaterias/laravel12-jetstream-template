<?php

declare(strict_types=1);

namespace App\Support\Billing;

class ExecutiveReportExecutionClassifier
{
    public function statusFor(bool $isReexecution): ExecutiveReportExportStatus
    {
        return $isReexecution
            ? ExecutiveReportExportStatus::Reexecuted
            : ExecutiveReportExportStatus::Completed;
    }

    public function completionEventFor(bool $isReexecution): string
    {
        return $isReexecution
            ? 'reexecuted'
            : 'completed';
    }
}
