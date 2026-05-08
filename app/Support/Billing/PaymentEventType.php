<?php

declare(strict_types=1);

namespace App\Support\Billing;

enum PaymentEventType: string
{
    case ChargeIssued = 'charge_issued';
    case ChargeFailed = 'charge_failed';
    case ChargeSettled = 'charge_settled';
    case ChargeExpired = 'charge_expired';
    case ChargeCancelled = 'charge_cancelled';
    case ReconciliationExceptionOpened = 'reconciliation_exception_opened';
    case ReconciliationExceptionResolved = 'reconciliation_exception_resolved';
    case ChargeRefunded = 'charge_refunded';
    case ChargebackConfirmed = 'chargeback_confirmed';
}
