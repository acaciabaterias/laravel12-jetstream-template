<?php

declare(strict_types=1);

namespace App\Support\Operations;

enum RunbookResultStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Partial = 'partial';
    case Failed = 'failed';
}
