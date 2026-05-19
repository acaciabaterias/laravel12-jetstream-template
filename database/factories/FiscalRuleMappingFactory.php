<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalRuleMapping>
 */
class FiscalRuleMappingFactory extends Factory
{
    protected $model = FiscalRuleMapping::class;

    public function definition(): array
    {
        return [
            'fiscal_rule_publication_record_id' => FiscalRulePublicationRecord::factory(),
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
            'classification_code' => '85072010',
            'operation_direction' => 'export',
            'validation_flags' => [
                'requires_ncm' => true,
                'requires_foreign_partner' => true,
            ],
            'metadata' => ['source' => 'test'],
        ];
    }
}
