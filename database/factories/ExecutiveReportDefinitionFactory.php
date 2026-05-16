<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExecutiveReportDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExecutiveReportDefinition>
 */
class ExecutiveReportDefinitionFactory extends Factory
{
    protected $model = ExecutiveReportDefinition::class;

    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->sentence(3),
            'description' => fake()->sentence(),
            'default_filters' => ['days' => 30],
            'visible_sections' => ['summary', 'plans'],
            'supported_formats' => ['excel', 'pdf'],
            'status' => 'active',
        ];
    }
}
