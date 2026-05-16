<?php

namespace Database\Factories;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\PlanoComercial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssinaturaPlataforma>
 */
class AssinaturaPlataformaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'plano_id' => PlanoComercial::factory(),
            'status' => 'active',
            'data_inicio' => now()->subDay(),
            'data_proximo_ciclo' => now()->addMonth(),
            'data_termino' => null,
            'grace_ends_at' => null,
            'blocked_at' => null,
            'blocked_reason' => null,
            'reactivated_at' => null,
            'cancel_reason' => null,
            'observacoes' => null,
            'metadata' => ['origem' => 'factory'],
        ];
    }
}
