<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalTaxProfile;

class PlatformFiscalTaxProfileRules
{
    /**
     * @param  array<string, mixed>  $scenarioMapping
     * @return array<int, string>
     */
    public function validate(array $scenarioMapping): array
    {
        $messages = [];
        $taxProfile = $scenarioMapping['tax_profile'] ?? null;
        $scenarioKey = (string) ($scenarioMapping['scenario_key'] ?? 'n/d');

        if (! is_array($taxProfile)) {
            return [sprintf('O cenario %s exige um perfil tributario material.', $scenarioKey)];
        }

        $taxRegime = (string) ($taxProfile['tax_regime'] ?? '');
        $originState = $this->normalizeState($taxProfile['origin_state'] ?? null);
        $destinationState = $this->normalizeState($taxProfile['destination_state'] ?? null);
        $partnerType = $taxProfile['partner_type'] ?? null;
        $operationPurpose = $taxProfile['operation_purpose'] ?? null;

        if (! filled($taxProfile['ncm_code'] ?? null)) {
            $messages[] = sprintf('O cenario %s exige NCM de referencia no perfil tributario.', $scenarioKey);
        }

        if (! in_array($taxRegime, (array) config('platform_fiscal_rules.supported_tax_regimes', []), true)) {
            $messages[] = sprintf('O cenario %s possui regime tributario nao suportado.', $scenarioKey);
        }

        if ($taxRegime === 'simple_national' && ! filled($taxProfile['csosn_code'] ?? null)) {
            $messages[] = sprintf('O cenario %s exige CSOSN quando o regime tributario for simple_national.', $scenarioKey);
        }

        if ($taxRegime !== '' && $taxRegime !== 'simple_national' && ! filled($taxProfile['cst_code'] ?? null)) {
            $messages[] = sprintf('O cenario %s exige CST quando o regime tributario nao for simple_national.', $scenarioKey);
        }

        if (($originState === null) !== ($destinationState === null)) {
            $messages[] = sprintf('O cenario %s deve informar origem e destino em conjunto para regras interestaduais.', $scenarioKey);
        }

        if ($originState !== null && $destinationState !== null && $originState !== $destinationState && ! filled($taxProfile['interstate_tax_rate'] ?? null)) {
            $messages[] = sprintf('O cenario %s exige aliquota interestadual quando origem e destino forem diferentes.', $scenarioKey);
        }

        if ($partnerType !== null && ! in_array($partnerType, (array) config('platform_fiscal_rules.supported_partner_types', []), true)) {
            $messages[] = sprintf('O cenario %s possui tipo de parceiro nao suportado.', $scenarioKey);
        }

        if ($operationPurpose !== null && ! in_array($operationPurpose, (array) config('platform_fiscal_rules.supported_operation_purposes', []), true)) {
            $messages[] = sprintf('O cenario %s possui finalidade operacional nao suportada.', $scenarioKey);
        }

        if (! is_array($taxProfile['tax_payload'] ?? null) || (array) $taxProfile['tax_payload'] === []) {
            $messages[] = sprintf('O cenario %s exige payload tributario material.', $scenarioKey);
        }

        return $messages;
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function resolveContext(array $scenario, array $filters = []): array
    {
        $originState = $this->normalizeState($filters['origin_state'] ?? null);
        $destinationState = $this->normalizeState($filters['destination_state'] ?? null);
        $flowScope = in_array($scenario['operation_direction'] ?? null, ['export', 'import'], true)
            ? 'international'
            : (($originState !== null && $destinationState !== null && $originState !== $destinationState) ? 'interstate' : 'intrastate');

        return [
            'origin_state' => $originState,
            'destination_state' => $destinationState,
            'partner_type' => $filters['partner_type'] ?? null,
            'operation_purpose' => $filters['operation_purpose'] ?? null,
            'tax_regime' => $filters['tax_regime'] ?? null,
            'flow_scope' => $flowScope,
            'is_interstate' => $originState !== null && $destinationState !== null && $originState !== $destinationState,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public function matchesContext(FiscalTaxProfile $taxProfile, array $context): bool
    {
        foreach (['origin_state', 'destination_state', 'partner_type', 'operation_purpose', 'tax_regime'] as $field) {
            $profileValue = $taxProfile->{$field};
            $contextValue = $context[$field] ?? null;

            if ($profileValue !== null && $contextValue !== null && (string) $profileValue !== (string) $contextValue) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function serialize(FiscalTaxProfile $taxProfile, array $context = []): array
    {
        return [
            'ncm_code' => $taxProfile->ncm_code,
            'tax_regime' => $taxProfile->tax_regime,
            'cst_code' => $taxProfile->cst_code,
            'csosn_code' => $taxProfile->csosn_code,
            'partner_type' => $taxProfile->partner_type,
            'operation_purpose' => $taxProfile->operation_purpose,
            'origin_state' => $taxProfile->origin_state,
            'destination_state' => $taxProfile->destination_state,
            'interstate_tax_rate' => $taxProfile->interstate_tax_rate !== null ? (string) $taxProfile->interstate_tax_rate : null,
            'tax_payload' => (array) $taxProfile->tax_payload,
            'context_match' => $context === [] ? true : $this->matchesContext($taxProfile, $context),
        ];
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function fallbackProfile(array $scenario, array $context = []): array
    {
        $fallback = (array) config(sprintf('platform_fiscal_rules.fallback_rules.scenarios.%s.tax_profile', $scenario['scenario_key']), []);
        $defaultProfile = (array) config('platform_fiscal_rules.fallback_rules.default_tax_profile', []);
        $resolved = array_merge($defaultProfile, $fallback);

        return [
            'ncm_code' => $resolved['ncm_code'] ?? null,
            'tax_regime' => $resolved['tax_regime'] ?? 'regular',
            'cst_code' => $resolved['cst_code'] ?? null,
            'csosn_code' => $resolved['csosn_code'] ?? null,
            'partner_type' => $context['partner_type'] ?? null,
            'operation_purpose' => $context['operation_purpose'] ?? null,
            'origin_state' => $context['origin_state'] ?? null,
            'destination_state' => $context['destination_state'] ?? null,
            'interstate_tax_rate' => $resolved['interstate_tax_rate'] ?? null,
            'tax_payload' => (array) ($resolved['tax_payload'] ?? []),
        ];
    }

    /**
     * @param  array<string, mixed>  $scenarioMapping
     * @return array<int, array<string, mixed>>
     */
    public function issuePayloads(array $scenarioMapping): array
    {
        $taxProfile = $scenarioMapping['tax_profile'] ?? null;
        $scenarioKey = (string) ($scenarioMapping['scenario_key'] ?? 'n/d');
        $issues = [];

        if (! is_array($taxProfile)) {
            return [[
                'scenario_key' => $scenarioKey,
                'issue_type' => 'material_tax_profile_missing',
                'issue_payload' => ['reason' => 'tax_profile_not_array'],
            ]];
        }

        $originState = $this->normalizeState($taxProfile['origin_state'] ?? null);
        $destinationState = $this->normalizeState($taxProfile['destination_state'] ?? null);

        if (! filled($taxProfile['ncm_code'] ?? null)) {
            $issues[] = [
                'scenario_key' => $scenarioKey,
                'issue_type' => 'tax_profile_gap',
                'issue_payload' => ['field' => 'ncm_code'],
            ];
        }

        if (($taxProfile['tax_regime'] ?? null) === 'simple_national' && ! filled($taxProfile['csosn_code'] ?? null)) {
            $issues[] = [
                'scenario_key' => $scenarioKey,
                'issue_type' => 'missing_csosn',
                'issue_payload' => ['tax_regime' => 'simple_national'],
            ];
        }

        if (($taxProfile['tax_regime'] ?? null) !== 'simple_national' && filled($taxProfile['tax_regime'] ?? null) && ! filled($taxProfile['cst_code'] ?? null)) {
            $issues[] = [
                'scenario_key' => $scenarioKey,
                'issue_type' => 'missing_cst',
                'issue_payload' => ['tax_regime' => $taxProfile['tax_regime'] ?? null],
            ];
        }

        if ($originState !== null && $destinationState !== null && $originState !== $destinationState && ! filled($taxProfile['interstate_tax_rate'] ?? null)) {
            $issues[] = [
                'scenario_key' => $scenarioKey,
                'issue_type' => 'missing_interstate_rate',
                'issue_payload' => [
                    'origin_state' => $originState,
                    'destination_state' => $destinationState,
                ],
            ];
        }

        return $issues;
    }

    private function normalizeState(mixed $state): ?string
    {
        if (! is_string($state) || trim($state) === '') {
            return null;
        }

        return strtoupper(trim($state));
    }
}
