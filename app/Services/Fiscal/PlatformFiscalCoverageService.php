<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

class PlatformFiscalCoverageService
{
    /**
     * @param  array<int, array<string, mixed>>  $catalogEntries
     * @param  array<int, array<string, mixed>>  $scenarioMappings
     * @return array<string, mixed>
     */
    public function snapshot(array $catalogEntries, array $scenarioMappings): array
    {
        $requiredScenarios = (array) config('platform_fiscal_rules.required_scenarios', []);
        $catalogByCfop = collect($catalogEntries)->keyBy('cfop_code');
        $mappingsByScenario = collect($scenarioMappings)->keyBy('scenario_key');
        $missingScenarios = [];
        $invalidMappings = [];

        foreach ($requiredScenarios as $scenarioKey => $definition) {
            $mapping = $mappingsByScenario->get($scenarioKey);

            if ($mapping === null) {
                $missingScenarios[] = $scenarioKey;

                continue;
            }

            if (! $catalogByCfop->has($mapping['cfop_code'] ?? null)) {
                $invalidMappings[] = [
                    'scenario_key' => $scenarioKey,
                    'issue_type' => 'unknown_cfop',
                    'expected_direction' => $definition['operation_direction'] ?? null,
                ];

                continue;
            }

            if (($mapping['operation_direction'] ?? null) !== ($definition['operation_direction'] ?? null)) {
                $invalidMappings[] = [
                    'scenario_key' => $scenarioKey,
                    'issue_type' => 'direction_mismatch',
                    'expected_direction' => $definition['operation_direction'] ?? null,
                    'received_direction' => $mapping['operation_direction'] ?? null,
                ];
            }
        }

        $requiredScenarioCount = count($requiredScenarios);
        $coveredScenarios = $requiredScenarioCount - count($missingScenarios);

        return [
            'required_scenarios' => $requiredScenarioCount,
            'configured_scenarios' => count($mappingsByScenario),
            'covered_scenarios' => $coveredScenarios,
            'missing_scenarios' => array_values($missingScenarios),
            'invalid_mappings' => array_values($invalidMappings),
            'catalog_size' => count($catalogByCfop),
            'coverage_ratio' => $requiredScenarioCount === 0
                ? 1.0
                : round($coveredScenarios / $requiredScenarioCount, 4),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $catalogEntries
     * @param  array<int, array<string, mixed>>  $scenarioMappings
     * @param  array<string, mixed>  $coverageSnapshot
     * @return array<int, array<string, mixed>>
     */
    public function issueReports(array $catalogEntries, array $scenarioMappings, array $coverageSnapshot): array
    {
        $reports = [];
        $catalogByCfop = collect($catalogEntries)->keyBy('cfop_code');

        foreach ((array) ($coverageSnapshot['missing_scenarios'] ?? []) as $scenarioKey) {
            $reports[] = [
                'scenario_key' => $scenarioKey,
                'issue_type' => 'missing_scenario',
                'severity' => 'warning',
                'resolution_status' => 'open',
                'detected_at' => now(),
                'issue_payload' => [
                    'required' => true,
                ],
            ];
        }

        foreach ((array) ($coverageSnapshot['invalid_mappings'] ?? []) as $invalidMapping) {
            $reports[] = [
                'scenario_key' => $invalidMapping['scenario_key'],
                'issue_type' => $invalidMapping['issue_type'],
                'severity' => 'critical',
                'resolution_status' => 'open',
                'detected_at' => now(),
                'issue_payload' => $invalidMapping,
            ];
        }

        foreach ($scenarioMappings as $mapping) {
            $catalogEntry = $catalogByCfop->get($mapping['cfop_code'] ?? null);

            if ($catalogEntry === null) {
                continue;
            }

            if (($catalogEntry['operation_direction'] ?? null) !== ($mapping['operation_direction'] ?? null)) {
                $reports[] = [
                    'scenario_key' => $mapping['scenario_key'],
                    'issue_type' => 'catalog_direction_mismatch',
                    'severity' => 'critical',
                    'resolution_status' => 'open',
                    'detected_at' => now(),
                    'issue_payload' => [
                        'catalog_direction' => $catalogEntry['operation_direction'] ?? null,
                        'mapping_direction' => $mapping['operation_direction'] ?? null,
                    ],
                ];
            }
        }

        return $reports;
    }
}
