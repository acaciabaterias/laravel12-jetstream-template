<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum CommercialAnalyticsRiskType: string
{
    case Churn = 'churn';
    case Delinquency = 'delinquency';
    case RecoveryStall = 'recovery_stall';
    case PaymentFailure = 'payment_failure';
}
