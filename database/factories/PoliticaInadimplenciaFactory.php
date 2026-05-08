<?php

namespace Database\Factories;

use App\Models\PoliticaInadimplencia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoliticaInadimplencia>
 */
class PoliticaInadimplenciaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
            'grace_period_days' => fake()->numberBetween(0, 10),
            'block_after_days' => fake()->numberBetween(1, 30),
            'reactivation_mode' => 'automatic',
            'notification_profile' => ['email' => true],
            'status' => 'active',
        ];
    }
}
