<?php

declare(strict_types=1);

namespace App\Support\Platform;

enum PlatformCurrencyPublicationStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Superseded = 'superseded';
    case RolledBack = 'rolled_back';
}
