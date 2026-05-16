<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\IndicadorRecuperacaoReceita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndicadorRecuperacaoReceita>
 */
class IndicadorRecuperacaoReceitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference_date' => now()->toDateString(),
            'channel' => fake()->randomElement(['email', 'whatsapp', 'phone']),
            'stage_name' => fake()->randomElement(['d1', 'd3', 'escalated']),
            'open_cases' => fake()->numberBetween(0, 20),
            'escalated_cases' => fake()->numberBetween(0, 10),
            'recovered_cases' => fake()->numberBetween(0, 15),
            'broken_promises' => fake()->numberBetween(0, 5),
            'recovery_amount' => fake()->randomFloat(2, 0, 5000),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
