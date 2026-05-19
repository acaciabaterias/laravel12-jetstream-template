<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformCurrencyPublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformCurrencyPublicationRecord>
 */
class PlatformCurrencyPublicationRecordFactory extends Factory
{
    protected $model = PlatformCurrencyPublicationRecord::class;

    public function definition(): array
    {
        return [
            'release_key' => fake()->unique()->slug(),
            'status' => 'draft',
            'base_currency_code' => 'BRL',
            'default_currency_code' => 'USD',
            'supported_currencies' => ['BRL', 'USD', 'EUR'],
            'rate_snapshot' => [
                'BRL' => ['rate_against_base' => '1.00000000', 'inverse_rate' => '1.00000000'],
                'USD' => ['rate_against_base' => '5.42000000', 'inverse_rate' => '0.18450185'],
                'EUR' => ['rate_against_base' => '5.93000000', 'inverse_rate' => '0.16863406'],
            ],
            'coverage_snapshot' => [
                'required_pairs' => 3,
                'configured_pairs' => 3,
                'missing_pairs' => [],
                'coverage_ratio' => 1.0,
            ],
            'metadata' => ['source' => 'test'],
        ];
    }
}
