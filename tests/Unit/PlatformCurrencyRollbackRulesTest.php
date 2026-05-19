<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\PlatformCurrencyPublicationRecord;
use App\Services\Platform\PlatformCurrencyRollbackRules;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Illuminate\Support\Collection;
use Tests\TestCase;

class PlatformCurrencyRollbackRulesTest extends TestCase
{
    public function test_it_finds_the_last_restorable_currency_publication(): void
    {
        $current = PlatformCurrencyPublicationRecord::factory()->make([
            'id' => 10,
            'status' => PlatformCurrencyPublicationStatus::Active->value,
        ]);
        $restorable = PlatformCurrencyPublicationRecord::factory()->make([
            'id' => 9,
            'status' => PlatformCurrencyPublicationStatus::Superseded->value,
        ]);

        $publication = app(PlatformCurrencyRollbackRules::class)->findRestorablePublication(
            new Collection([$current, $restorable]),
            10,
        );

        $this->assertSame(9, $publication?->id);
    }
}
