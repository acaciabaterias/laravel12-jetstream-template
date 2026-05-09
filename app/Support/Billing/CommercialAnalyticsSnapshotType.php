<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum CommercialAnalyticsSnapshotType: string
{
    case Executive = 'executive';
    case Cohort = 'cohort';
    case Channel = 'channel';
    case Drilldown = 'drilldown';
}
