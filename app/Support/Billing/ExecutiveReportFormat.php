<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum ExecutiveReportFormat: string
{
    case Excel = 'excel';
    case Pdf = 'pdf';

    public function extension(): string
    {
        return match ($this) {
            self::Excel => 'xlsx',
            self::Pdf => 'pdf',
        };
    }
}
