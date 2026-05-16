<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RetornoPagamentoSaaS;
use App\Models\CobrancaSaaSExterna;
use App\Models\GatewayCobrancaSaaS;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RetornoPagamentoSaaS>
 */
class RetornoPagamentoSaaSFactory extends Factory
{
    public function definition(): array
    {
        return [
            'gateway_cobranca_saas_id' => GatewayCobrancaSaaS::factory(),
            'cobranca_saas_externa_id' => CobrancaSaaSExterna::factory(),
            'source_type' => 'webhook',
            'external_event_id' => fake()->uuid(),
            'external_reference' => fake()->bothify('charge-####'),
            'event_type' => fake()->randomElement(['payment_received', 'charge_expired']),
            'payload' => ['status' => 'paid'],
            'received_at' => now(),
            'processed_at' => null,
            'processing_status' => 'pending',
            'processing_error' => null,
            'idempotency_key' => fake()->unique()->sha1(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
