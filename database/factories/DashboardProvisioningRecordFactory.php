<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DashboardProvisioningRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DashboardProvisioningRecord>
 */
class DashboardProvisioningRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'package_name' => fake()->randomElement(['ops-overview', 'backbone-alerts']),
            'version' => fake()->numerify('v#.#.#'),
            'environment' => fake()->randomElement(['staging', 'production']),
            'applied_at' => now()->subMinutes(30),
            'validated_at' => now()->subMinutes(5),
            'rollback_version' => null,
            'status' => fake()->randomElement(['pending', 'applied', 'failed', 'rolled_back']),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
