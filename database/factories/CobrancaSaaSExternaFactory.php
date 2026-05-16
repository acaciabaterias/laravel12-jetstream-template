<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Models\GatewayCobrancaSaaS;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CobrancaSaaSExterna>
 */
class CobrancaSaaSExternaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fatura_saas_id' => FaturaSaaS::factory(),
            'gateway_cobranca_saas_id' => GatewayCobrancaSaaS::factory(),
            'external_charge_id' => fake()->uuid(),
            'external_reference' => fake()->unique()->bothify('charge-####'),
            'payment_channel' => fake()->randomElement(['boleto', 'pix']),
            'status' => 'pending',
            'valor_emitido' => fake()->randomFloat(2, 99, 999),
            'vencimento_emitido' => now()->addWeek(),
            'issued_at' => now(),
            'paid_at' => null,
            'cancelled_at' => null,
            'failure_reason' => null,
            'idempotency_key' => fake()->unique()->sha1(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
