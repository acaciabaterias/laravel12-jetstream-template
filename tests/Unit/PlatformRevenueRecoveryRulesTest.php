<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\CasoRecuperacaoReceita;
use App\Models\FaturaSaaS;
use App\Services\Billing\PaymentPromiseService;
use App\Services\Billing\PlatformRevenueRecoveryEventPublisher;
use App\Services\Billing\RevenueRecoveryEscalationService;
use App\Services\Contracts\Integration\EventPublisherContract;
use App\Services\Integration\IntegrationStorageManager;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Mockery;
use Tests\TestCase;

class PlatformRevenueRecoveryRulesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_escalation_score_considers_overdue_days_and_severity(): void
    {
        $publisher = new PlatformRevenueRecoveryEventPublisher(
            eventPublisher: Mockery::mock(EventPublisherContract::class),
            integrationStorageManager: new IntegrationStorageManager,
        );
        $service = new RevenueRecoveryEscalationService($publisher);
        $case = new CasoRecuperacaoReceita([
            'status' => RevenueRecoveryCaseStatus::Open->value,
            'severity' => RevenueRecoverySeverity::High->value,
        ]);
        $case->setRelation('fatura', new FaturaSaaS([
            'vencimento' => now()->subDays(8),
        ]));
        $case->setRelation('acoes', collect([
            new AcaoRecuperacaoReceita(['status' => 'failed']),
            new AcaoRecuperacaoReceita(['status' => 'failed']),
        ]));

        $this->assertTrue($service->shouldEscalate($case));
    }

    public function test_promise_service_only_suspends_contact_channels(): void
    {
        $publisher = new PlatformRevenueRecoveryEventPublisher(
            eventPublisher: Mockery::mock(EventPublisherContract::class),
            integrationStorageManager: new IntegrationStorageManager,
        );
        $service = new PaymentPromiseService($publisher);

        $this->assertTrue($service->shouldSuspendAction('email'));
        $this->assertTrue($service->shouldSuspendAction('whatsapp'));
        $this->assertFalse($service->shouldSuspendAction('internal_task'));
    }
}
