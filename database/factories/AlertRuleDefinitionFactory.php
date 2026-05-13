<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AlertRuleDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AlertRuleDefinition>
 */
class AlertRuleDefinitionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'flow_name' => fake()->randomElement(['integration_backbone', 'platform_payments', 'platform_recovery']),
            'rule_name' => fake()->unique()->slug(3),
            'severity' => fake()->randomElement(['warning', 'critical']),
            'version' => fake()->numerify('v#'),
            'condition_summary' => fake()->sentence(),
            'status' => fake()->randomElement(['pending', 'applied', 'failed', 'rolled_back']),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
