<?php

declare(strict_types=1);

namespace App\Support\Platform;

enum PlatformCurrencyIssueSeverity: string
{
    case Warning = 'warning';
    case Critical = 'critical';
}
