<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum ThemePublicationStatus: string
{
    case Pending = 'pending';
    case Published = 'published';
    case Rejected = 'rejected';
}
