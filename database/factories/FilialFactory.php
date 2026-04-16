<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Filial>
 */
class FilialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'cnpj' => fake()->unique()->numerify('##############'),
            'active' => true,
            'email_contato' => fake()->safeEmail(),
            'telefone' => fake()->phoneNumber(),
            'plano' => 'essential',
            'status_assinatura' => 'active',
        ];
    }
}
