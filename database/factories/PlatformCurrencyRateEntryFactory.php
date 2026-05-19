<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformCurrencyPublicationRecord;
use App\Models\PlatformCurrencyRateEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformCurrencyRateEntry>
 */
class PlatformCurrencyRateEntryFactory extends Factory
{
    protected $model = PlatformCurrencyRateEntry::class;

    public function definition(): array
    {
        return [
            'platform_currency_publication_record_id' => PlatformCurrencyPublicationRecord::factory(),
            'currency_code' => 'USD',
            'rate_against_base' => 5.42000000,
            'inverse_rate' => 0.18450185,
            'effective_at' => now(),
            'metadata' => ['source' => 'test'],
        ];
    }
}
