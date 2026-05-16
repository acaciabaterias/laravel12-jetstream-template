<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CobrancaSaaSExterna;
use App\Services\Billing\PaymentReconciliationService;
use App\Services\Billing\PlatformPaymentsEventPublisher;
use App\Support\Billing\PaymentReconciliationStatus;
use PHPUnit\Framework\TestCase;

class PlatformPaymentsReconciliationRuleTest extends TestCase
{
    public function test_it_matches_when_reference_and_amount_are_consistent(): void
    {
        $service = new PaymentReconciliationService(
            saasInvoiceService: $this->createMock(\App\Services\Billing\SaasInvoiceService::class),
            delinquencyPolicyEvaluator: $this->createMock(\App\Services\Billing\DelinquencyPolicyEvaluator::class),
            eventPublisher: $this->createMock(PlatformPaymentsEventPublisher::class),
        );
        $charge = new CobrancaSaaSExterna([
            'external_reference' => 'saas-1-1',
            'valor_emitido' => 150.00,
        ]);

        $outcome = $service->determineOutcome($charge, 150.00, 'saas-1-1');

        $this->assertSame(PaymentReconciliationStatus::Matched, $outcome['status']);
        $this->assertSame(0.0, $outcome['difference']);
    }

    public function test_it_opens_exception_when_amount_or_reference_diverge(): void
    {
        $service = new PaymentReconciliationService(
            saasInvoiceService: $this->createMock(\App\Services\Billing\SaasInvoiceService::class),
            delinquencyPolicyEvaluator: $this->createMock(\App\Services\Billing\DelinquencyPolicyEvaluator::class),
            eventPublisher: $this->createMock(PlatformPaymentsEventPublisher::class),
        );
        $charge = new CobrancaSaaSExterna([
            'external_reference' => 'saas-1-1',
            'valor_emitido' => 150.00,
        ]);

        $outcome = $service->determineOutcome($charge, 120.00, 'saas-1-1');

        $this->assertSame(PaymentReconciliationStatus::Exception, $outcome['status']);
        $this->assertSame(-30.0, $outcome['difference']);
    }
}
