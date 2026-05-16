<?php

namespace Database\Factories;

use App\Models\AssinaturaPlataforma;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaturaSaaS>
 */
class FaturaSaaSFactory extends Factory
{
    public function definition(): array
    {
        $vencimento = now()->addDays(fake()->numberBetween(3, 30));

        return [
            'assinatura_id' => AssinaturaPlataforma::factory(),
            'cliente_id' => Cliente::factory(),
            'referencia' => now()->format('Y-m'),
            'vencimento' => $vencimento,
            'valor' => fake()->randomFloat(2, 99, 999),
            'valor_pago' => null,
            'status' => 'pending',
            'external_invoice_id' => fake()->optional()->uuid(),
            'billing_channel' => 'manual',
            'paid_at' => null,
            'payload_gateway' => ['provider' => 'manual'],
            'metadata' => ['source' => 'factory'],
        ];
    }
}
