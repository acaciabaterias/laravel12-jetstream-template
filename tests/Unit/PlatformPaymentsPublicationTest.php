<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Services\Billing\PlatformPaymentsEventPublisher;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Mockery;
use Tests\TestCase;

class PlatformPaymentsPublicationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_skips_publication_when_feature_flag_is_disabled(): void
    {
        config()->set('platform_payments.events.publish_to_backbone', false);

        $eventPublisher = Mockery::mock(EventPublisherContract::class);
        $eventPublisher->shouldNotReceive('publish');

        $service = new PlatformPaymentsEventPublisher(
            eventPublisher: $eventPublisher,
            integrationStorageManager: new IntegrationStorageManager,
        );

        $invoice = new FaturaSaaS(['id' => 55]);
        $invoice->setRelation('cliente', new Cliente(['subdominio' => 'tenant-disabled']));

        $service->publish(
            eventType: 'COBRANCA_SAAS_LIQUIDADA',
            faturaSaaS: $invoice,
            payload: ['invoice_id' => 55],
            consumers: ['platform'],
        );

        $this->assertTrue(true);
    }
}
