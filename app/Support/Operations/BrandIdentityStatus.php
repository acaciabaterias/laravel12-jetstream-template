<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum BrandIdentityStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Archived = 'archived';
}
