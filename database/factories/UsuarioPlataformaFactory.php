<?php

namespace Database\Factories;

use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<UsuarioPlataforma>
 */
class UsuarioPlataformaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'papel' => 'support',
            'ativo' => true,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn (): array => [
            'papel' => 'super_admin',
        ]);
    }

    public function billing(): static
    {
        return $this->state(fn (): array => [
            'papel' => 'billing',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'ativo' => false,
        ]);
    }
}
