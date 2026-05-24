<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;
use App\Models\FiscalTaxProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalTaxProfile>
 */
class FiscalTaxProfileFactory extends Factory
{
    protected $model = FiscalTaxProfile::class;

    public function definition(): array
    {
        return [
            'fiscal_rule_mapping_id' => FiscalRuleMapping::factory(),
            'fiscal_rule_publication_record_id' => FiscalRulePublicationRecord::factory(),
            'scenario_key' => 'direct_export',
            'cfop_code' => '7101',
            'ncm_code' => '85072010',
            'tax_regime' => 'regular',
            'cst_code' => '041',
            'csosn_code' => null,
            'partner_type' => 'customer',
            'operation_purpose' => 'direct_export',
            'origin_state' => null,
            'destination_state' => null,
            'interstate_tax_rate' => null,
            'tax_payload' => [
                'ipi_rate' => 0,
                'pis_rate' => 0,
                'cofins_rate' => 0,
            ],
            'metadata' => ['source' => 'test'],
        ];
    }
}
