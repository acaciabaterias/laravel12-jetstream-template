<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\FiscalTaxProfile;
use App\Services\Fiscal\PlatformFiscalTaxProfileRules;
use Tests\TestCase;

class PlatformFiscalTaxProfileRulesTest extends TestCase
{
    public function test_regular_regime_requires_ncm_cst_and_interstate_rate_for_interstate_rules(): void
    {
        $messages = app(PlatformFiscalTaxProfileRules::class)->validate([
            'scenario_key' => 'interstate_resale',
            'tax_profile' => [
                'tax_regime' => 'regular',
                'origin_state' => 'SP',
                'destination_state' => 'RJ',
                'tax_payload' => ['icms_rate' => 12],
            ],
        ]);

        $this->assertContains('O cenario interstate_resale exige NCM de referencia no perfil tributario.', $messages);
        $this->assertContains('O cenario interstate_resale exige CST quando o regime tributario nao for simple_national.', $messages);
        $this->assertContains('O cenario interstate_resale exige aliquota interestadual quando origem e destino forem diferentes.', $messages);
    }

    public function test_simple_national_profile_uses_csosn_and_matches_context_constraints(): void
    {
        $rules = app(PlatformFiscalTaxProfileRules::class);
        $profile = new FiscalTaxProfile([
            'tax_regime' => 'simple_national',
            'csosn_code' => '500',
            'partner_type' => 'customer',
            'operation_purpose' => 'resale',
            'origin_state' => 'SP',
            'destination_state' => 'RJ',
            'tax_payload' => ['icms_rate' => 12],
        ]);

        $context = $rules->resolveContext([
            'scenario_key' => 'interstate_resale',
            'operation_direction' => 'domestic_out',
        ], [
            'origin_state' => 'SP',
            'destination_state' => 'RJ',
            'partner_type' => 'customer',
            'operation_purpose' => 'resale',
            'tax_regime' => 'simple_national',
        ]);

        $this->assertSame([], $rules->validate([
            'scenario_key' => 'interstate_resale',
            'tax_profile' => [
                'ncm_code' => '85072010',
                'tax_regime' => 'simple_national',
                'csosn_code' => '500',
                'partner_type' => 'customer',
                'operation_purpose' => 'resale',
                'origin_state' => 'SP',
                'destination_state' => 'RJ',
                'interstate_tax_rate' => 12,
                'tax_payload' => ['icms_rate' => 12],
            ],
        ]));
        $this->assertTrue($rules->matchesContext($profile, $context));
        $this->assertTrue($context['is_interstate']);
    }
}
