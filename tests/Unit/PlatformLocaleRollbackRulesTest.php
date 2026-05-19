<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\PlatformLocalePublicationRecord;
use App\Services\Platform\PlatformLocaleRollbackRules;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PlatformLocaleRollbackRulesTest extends TestCase
{
    public function test_it_finds_the_last_restorable_publication(): void
    {
        $rules = new PlatformLocaleRollbackRules;
        $current = PlatformLocalePublicationRecord::factory()->make([
            'id' => 2,
            'status' => PlatformLocalePublicationStatus::Active->value,
        ]);
        $baseline = PlatformLocalePublicationRecord::factory()->make([
            'id' => 1,
            'status' => PlatformLocalePublicationStatus::Superseded->value,
        ]);

        $restorable = $rules->findRestorablePublication(new Collection([$current, $baseline]), 2);

        $this->assertSame(1, $restorable?->id);
        $this->assertTrue($rules->canRollback($current, $restorable));
    }
}
