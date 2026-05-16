<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use App\Services\Billing\ExternalChargeIssuanceService;
use App\Services\Billing\PlatformPaymentsEventPublisher;
use PHPUnit\Framework\TestCase;

class PlatformPaymentsIdempotencyTest extends TestCase
{
    public function test_it_builds_a_stable_idempotency_key_for_the_same_charge_fingerprint(): void
    {
        $service = new ExternalChargeIssuanceService(
            $this->createMock(PlatformPaymentsEventPublisher::class),
        );
        $fatura = new FaturaSaaS([
            'id' => 15,
            'referencia' => '2026-05',
        ]);
        $gateway = new GatewayCobrancaSaaS([
            'id' => 3,
            'slug' => 'mock-gateway',
        ]);

        $firstKey = $service->buildIdempotencyKey($fatura, $gateway, 'boleto');
        $secondKey = $service->buildIdempotencyKey($fatura, $gateway, 'boleto');

        $this->assertSame($firstKey, $secondKey);
    }

    public function test_it_changes_the_idempotency_key_when_the_reissue_sequence_changes(): void
    {
        $service = new ExternalChargeIssuanceService(
            $this->createMock(PlatformPaymentsEventPublisher::class),
        );
        $fatura = new FaturaSaaS([
            'id' => 15,
            'referencia' => '2026-05',
        ]);
        $gateway = new GatewayCobrancaSaaS([
            'id' => 3,
            'slug' => 'mock-gateway',
        ]);

        $firstKey = $service->buildIdempotencyKey($fatura, $gateway, 'boleto', 1);
        $secondKey = $service->buildIdempotencyKey($fatura, $gateway, 'boleto', 2);

        $this->assertNotSame($firstKey, $secondKey);
    }
}
