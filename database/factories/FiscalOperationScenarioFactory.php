<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalOperationScenario;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalOperationScenario>
 */
class FiscalOperationScenarioFactory extends Factory
{
    protected $model = FiscalOperationScenario::class;

    public function definition(): array
    {
        return [
            'scenario_key' => fake()->unique()->randomElement(['direct_export', 'resale_import', 'indirect_export', 'industrial_import']),
            'display_name' => fake()->randomElement([
                'Direct export',
                'Resale import',
                'Indirect export',
                'Industrial import',
            ]),
            'operation_direction' => fake()->randomElement(['export', 'import']),
            'is_required' => true,
            'metadata' => ['source' => 'test'],
        ];
    }
}
