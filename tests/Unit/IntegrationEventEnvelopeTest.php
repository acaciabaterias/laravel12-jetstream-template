<?php

namespace Tests\Unit;

use App\Services\Integration\OutboxEventFactory;
use PHPUnit\Framework\TestCase;

class IntegrationEventEnvelopeTest extends TestCase
{
    public function test_it_normalizes_envelope_with_required_fields(): void
    {
        $factory = new OutboxEventFactory;

        $envelope = $factory->make(
            eventType: 'VALE_FATURADO',
            payload: ['vale_id' => 1],
            tenantExternalRef: 'tenant-a',
            idempotencyKey: 'key-1',
            correlationId: '2fd39e45-e889-418d-87f7-72c76e54adf1',
            eventVersion: 'v1',
            originContext: 'sales',
            causationId: '7d0213de-f9b3-44fe-80a1-379738c1a9b3',
            metadata: ['target' => 'broker:erp-backbone'],
        );

        $this->assertSame('VALE_FATURADO', $envelope['event_type']);
        $this->assertSame('v1', $envelope['event_version']);
        $this->assertSame('tenant-a', $envelope['tenant_external_ref']);
        $this->assertSame('key-1', $envelope['idempotency_key']);
        $this->assertSame('sales', $envelope['origin_context']);
        $this->assertArrayHasKey('occurred_at', $envelope);
        $this->assertArrayHasKey('available_at', $envelope);
        $this->assertSame(['vale_id' => 1], $envelope['payload']);
    }

    public function test_it_generates_correlation_id_when_missing(): void
    {
        $factory = new OutboxEventFactory;

        $envelope = $factory->make(
            eventType: 'COBRANCA_CRIAR_BOLETO',
            payload: ['vale_id' => 2],
            tenantExternalRef: 'tenant-b',
            idempotencyKey: 'key-2',
        );

        $this->assertMatchesRegularExpression(
            '/^[0-9a-fA-F-]{36}$/',
            $envelope['correlation_id']
        );
    }
}
