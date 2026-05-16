<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\SnapshotAnalyticsComercial;
use App\Services\Billing\PlatformCommercialAnalyticsEventPublisher;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use Mockery;
use Tests\TestCase;

class PlatformCommercialAnalyticsPublicationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_skips_publication_when_feature_flag_is_disabled(): void
    {
        config()->set('platform_commercial_analytics.events.publish_to_backbone', false);

        $eventPublisher = Mockery::mock(EventPublisherContract::class);
        $eventPublisher->shouldNotReceive('publish');

        $service = new PlatformCommercialAnalyticsEventPublisher(
            eventPublisher: $eventPublisher,
            integrationStorageManager: new IntegrationStorageManager,
        );

        $snapshot = new SnapshotAnalyticsComercial(['id' => 77]);

        $service->publish(
            eventType: 'SNAPSHOT_ANALYTICS_ATUALIZADO',
            snapshotAnalyticsComercial: $snapshot,
            payload: ['snapshot_id' => 77],
            consumers: ['platform'],
        );

        $this->assertTrue(true);
    }
}
