<?php

namespace Database\Factories;

use App\Models\PlanoComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanoComercial>
 */
class PlanoComercialFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(2, true),
            'slug' => fake()->unique()->slug(2),
            'preco_mensal' => fake()->randomFloat(2, 99, 999),
            'preco_anual' => fake()->randomFloat(2, 999, 9999),
            'periodicidade' => 'mensal',
            'max_usuarios' => fake()->numberBetween(3, 100),
            'max_estoque_itens' => fake()->numberBetween(100, 5000),
            'has_white_label' => fake()->boolean(),
            'has_support_priority' => fake()->boolean(),
            'ativo' => true,
            'recursos' => ['suporte' => 'padrao'],
            'beneficios' => ['dashboard' => true],
        ];
    }
}
