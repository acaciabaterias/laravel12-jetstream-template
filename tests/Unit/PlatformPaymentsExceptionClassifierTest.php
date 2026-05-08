<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CobrancaSaaSExterna;
use App\Services\Billing\PaymentReconciliationService;
use App\Services\Billing\PlatformPaymentsEventPublisher;
use App\Support\Billing\PaymentReconciliationStatus;
use PHPUnit\Framework\TestCase;

class PlatformPaymentsExceptionClassifierTest extends TestCase
{
    public function test_it_classifies_reference_mismatch_as_exception(): void
    {
        $service = new PaymentReconciliationService(
            saasInvoiceService: $this->createMock(\App\Services\Billing\SaasInvoiceService::class),
            delinquencyPolicyEvaluator: $this->createMock(\App\Services\Billing\DelinquencyPolicyEvaluator::class),
            eventPublisher: $this->createMock(PlatformPaymentsEventPublisher::class),
        );
        $charge = new CobrancaSaaSExterna([
            'external_reference' => 'saas-10-1',
            'valor_emitido' => 99.90,
        ]);

        $outcome = $service->determineOutcome($charge, 99.90, 'saas-other');

        $this->assertSame(PaymentReconciliationStatus::Exception, $outcome['status']);
        $this->assertSame('reference_mismatch', $outcome['reason']);
    }
}
