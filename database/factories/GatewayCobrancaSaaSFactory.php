<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GatewayCobrancaSaaS;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GatewayCobrancaSaaS>
 */
class GatewayCobrancaSaaSFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->company().' Gateway',
            'slug' => fake()->unique()->slug(2),
            'driver' => fake()->randomElement(['asaas', 'stripe', 'mock']),
            'status' => 'active',
            'supported_channels' => ['boleto', 'pix'],
            'credential_profile' => ['account' => fake()->uuid()],
            'timeout_seconds' => 30,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
