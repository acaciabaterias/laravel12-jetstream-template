<?php

namespace App\Support\Integration;

enum IntegrationFlowStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Processed = 'processed';
    case Failed = 'failed';
    case DeadLetter = 'dead_letter';
    case Replayed = 'replayed';
    case Skipped = 'skipped';
}
