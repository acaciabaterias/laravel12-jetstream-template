<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalRulePublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalRulePublicationRecord>
 */
class FiscalRulePublicationRecordFactory extends Factory
{
    protected $model = FiscalRulePublicationRecord::class;

    public function definition(): array
    {
        return [
            'release_key' => fake()->unique()->slug(),
            'status' => 'draft',
            'supported_scenarios' => ['direct_export', 'resale_import'],
            'catalog_snapshot' => [
                'cfops' => [
                    ['cfop_code' => '7101', 'operation_direction' => 'export'],
                    ['cfop_code' => '3101', 'operation_direction' => 'import'],
                ],
            ],
            'coverage_snapshot' => [
                'required_scenarios' => 2,
                'configured_scenarios' => 2,
                'missing_scenarios' => [],
                'coverage_ratio' => 1.0,
            ],
            'metadata' => ['source' => 'test'],
        ];
    }
}
