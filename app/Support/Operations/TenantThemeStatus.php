<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum TenantThemeStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case RolledBack = 'rolled_back';
}
