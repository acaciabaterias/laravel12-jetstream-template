<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WhiteLabelConfig>
 */
class WhiteLabelConfigFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'filial_id' => \App\Models\Filial::factory(),
            'logo_url' => fake()->imageUrl(200, 50, 'business'),
            'favicon_url' => fake()->imageUrl(32, 32),
            'cor_primaria' => fake()->safeHexColor(),
            'cor_secundaria' => fake()->safeHexColor(),
            'cor_fundo' => '#f9fafb',
            'titulo_login' => fake()->word(),
            'mostrar_marca_plataforma' => true,
        ];
    }
}
