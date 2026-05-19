<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;

class PlatformFiscalResolutionRules
{
    /**
     * @param  array<string, mixed>  $scenario
     */
    public function mappingMatchesScenario(array $scenario, FiscalRuleMapping $mapping): bool
    {
        return filled($mapping->cfop_code)
            && in_array($mapping->operation_direction, (array) config('platform_fiscal_rules.supported_directions', []), true)
            && $mapping->operation_direction === ($scenario['operation_direction'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $scenario
     * @return array<string, mixed>
     */
    public function fallbackForScenario(
        array $scenario,
        ?FiscalRulePublicationRecord $publication,
        ?FiscalRuleMapping $mapping = null,
    ): array {
        $fallbacks = (array) config('platform_fiscal_rules.fallback_rules.scenarios', []);
        $defaultFlags = (array) config('platform_fiscal_rules.fallback_rules.default_validation_flags', []);
        $scenarioFallback = (array) ($fallbacks[$scenario['scenario_key']] ?? []);

        return [
            'scenario_key' => $scenario['scenario_key'],
            'display_name' => $scenario['display_name'],
            'operation_direction' => $scenario['operation_direction'],
            'resolution_type' => 'governed_fallback',
            'cfop_code' => $scenarioFallback['cfop_code'] ?? null,
            'classification_code' => $scenarioFallback['classification_code']
                ?? config('platform_fiscal_rules.fallback_rules.default_classification_code', 'MANUAL_REVIEW'),
            'validation_flags' => array_values(array_unique(array_merge(
                array_keys(array_filter($defaultFlags)),
                array_keys(array_filter((array) ($scenarioFallback['validation_flags'] ?? []))),
            ))),
            'source_publication_id' => $publication?->id,
            'issue' => [
                'code' => $this->issueCode($publication, $mapping),
                'message' => $this->issueMessage($publication, $mapping, (string) $scenario['display_name']),
            ],
        ];
    }

    private function issueCode(?FiscalRulePublicationRecord $publication, ?FiscalRuleMapping $mapping): string
    {
        if ($publication === null) {
            return 'missing_active_publication';
        }

        if ($mapping === null) {
            return 'missing_mapping';
        }

        return 'direction_mismatch';
    }

    private function issueMessage(
        ?FiscalRulePublicationRecord $publication,
        ?FiscalRuleMapping $mapping,
        string $displayName,
    ): string {
        if ($publication === null) {
            return sprintf('No active fiscal publication is available for %s.', $displayName);
        }

        if ($mapping === null) {
            return sprintf('No active mapping was found for %s.', $displayName);
        }

        return sprintf('The active mapping for %s is incompatible with the scenario direction.', $displayName);
    }
}
