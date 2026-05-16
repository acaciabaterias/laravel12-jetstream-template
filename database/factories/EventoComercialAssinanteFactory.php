<?php

namespace Database\Factories;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\EventoComercialAssinante;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventoComercialAssinante>
 */
class EventoComercialAssinanteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'assinatura_id' => AssinaturaPlataforma::factory(),
            'actor_user_id' => UsuarioPlataforma::factory(),
            'event_type' => 'subscription_activated',
            'before_state' => ['status' => 'draft'],
            'after_state' => ['status' => 'active'],
            'effective_at' => now(),
            'reason' => fake()->sentence(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
