<?php

declare(strict_types=1);

namespace App\Support\Fiscal;

enum FiscalRuleIssueSeverity: string
{
    case Warning = 'warning';
    case Critical = 'critical';
}
