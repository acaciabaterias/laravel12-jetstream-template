<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TuningChangeRecord;
use App\Services\Operations\CriticalLoadTuningLifecycleService;
use InvalidArgumentException;
use Tests\NonDatabaseTestCase;

class CriticalLoadTuningRulesTest extends NonDatabaseTestCase
{
    public function test_it_blocks_promotion_of_pending_or_rollback_recommended_tuning(): void
    {
        $service = app(CriticalLoadTuningLifecycleService::class);
        $pending = new TuningChangeRecord([
            'flow_name' => 'integration_backbone',
            'environment' => 'staging',
            'change_key' => 'pending-change',
            'hypothesis_summary' => 'Hipotese pendente.',
            'change_type' => 'index',
            'status' => 'pending',
            'rollback_recommended' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->promote($pending);
    }

    public function test_it_blocks_rollback_when_not_recommended(): void
    {
        $service = app(CriticalLoadTuningLifecycleService::class);
        $validated = new TuningChangeRecord([
            'flow_name' => 'integration_backbone',
            'environment' => 'staging',
            'change_key' => 'validated-change',
            'hypothesis_summary' => 'Hipotese validada.',
            'change_type' => 'index',
            'status' => 'validated',
            'rollback_recommended' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->rollback($validated);
    }
}
