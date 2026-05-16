<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CasoRecuperacaoReceita;
use App\Services\Billing\PlatformRevenueRecoveryEventPublisher;
use App\Services\Billing\RevenueRecoveryActionScheduler;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Mockery;
use Tests\TestCase;

class PlatformRevenueRecoveryIdempotencyTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_generates_the_same_idempotency_key_for_the_same_stage_and_channel(): void
    {
        $publisher = new PlatformRevenueRecoveryEventPublisher(
            eventPublisher: Mockery::mock(EventPublisherContract::class),
            integrationStorageManager: new IntegrationStorageManager,
        );
        $scheduler = new RevenueRecoveryActionScheduler($publisher);
        $case = new CasoRecuperacaoReceita([
            'id' => 55,
            'fatura_saas_id' => 88,
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'severity' => RevenueRecoverySeverity::Medium->value,
        ]);
        $case->exists = true;

        $first = $scheduler->makeIdempotencyKey($case, 'd1', 'email');
        $second = $scheduler->makeIdempotencyKey($case, 'd1', 'email');
        $different = $scheduler->makeIdempotencyKey($case, 'd3', 'whatsapp');

        $this->assertSame($first, $second);
        $this->assertNotSame($first, $different);
    }
}
