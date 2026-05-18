<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\RecoveryAutomationPolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecoveryAutomationPolicyVersion>
 */
class RecoveryAutomationPolicyVersionFactory extends Factory
{
    protected $model = RecoveryAutomationPolicyVersion::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'status' => 'draft',
            'scope_filters' => ['segment' => 'default'],
            'guardrail_rules' => ['max_dispatches_per_day' => 3, 'cooldown_hours' => 24],
            'fallback_matrix' => ['primary' => 'whatsapp', 'fallbacks' => ['email', 'manual_follow_up']],
            'metadata' => ['source' => 'test'],
        ];
    }
}
