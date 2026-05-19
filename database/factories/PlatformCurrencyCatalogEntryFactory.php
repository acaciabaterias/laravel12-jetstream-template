<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformCurrencyCatalogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformCurrencyCatalogEntry>
 */
class PlatformCurrencyCatalogEntryFactory extends Factory
{
    protected $model = PlatformCurrencyCatalogEntry::class;

    public function definition(): array
    {
        return [
            'currency_code' => fake()->unique()->randomElement(['BRL', 'USD', 'EUR']),
            'display_name' => fake()->randomElement(['Brazilian Real', 'US Dollar', 'Euro']),
            'symbol' => fake()->randomElement(['R$', '$', '€']),
            'decimal_scale' => 2,
            'is_enabled' => true,
            'metadata' => ['source' => 'test'],
        ];
    }
}
