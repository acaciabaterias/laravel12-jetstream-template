<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\CasoRecuperacaoReceita;
use App\Services\Billing\PlatformRevenueRecoverySummaryService;
use App\Support\Billing\RevenueRecoveryCaseStatus;
use Tests\TestCase;

class PlatformRevenueRecoverySummaryTest extends TestCase
{
    public function test_it_flags_reengagement_eligibility_only_for_recovered_cases(): void
    {
        config()->set('platform_revenue_recovery.escalation.allow_reengagement', true);

        $service = new PlatformRevenueRecoverySummaryService;
        $recoveredCase = new CasoRecuperacaoReceita([
            'status' => RevenueRecoveryCaseStatus::Recovered->value,
        ]);
        $openCase = new CasoRecuperacaoReceita([
            'status' => RevenueRecoveryCaseStatus::Open->value,
        ]);

        $recoveredCase->setRelation('compromissos', collect());
        $openCase->setRelation('compromissos', collect());

        $this->assertTrue($service->isEligibleForReengagement($recoveredCase));
        $this->assertFalse($service->isEligibleForReengagement($openCase));
    }
}
