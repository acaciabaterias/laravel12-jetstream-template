<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CasoRecuperacaoReceita;
use App\Models\CompromissoPagamento;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompromissoPagamento>
 */
class CompromissoPagamentoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'caso_recuperacao_receita_id' => CasoRecuperacaoReceita::factory(),
            'promised_amount' => fake()->randomFloat(2, 50, 500),
            'promised_date' => now()->addDays(3)->toDateString(),
            'status' => 'open',
            'recorded_by_user_id' => UsuarioPlataforma::factory()->billing(),
            'notes' => fake()->sentence(),
            'suspends_until' => now()->addDays(3),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
