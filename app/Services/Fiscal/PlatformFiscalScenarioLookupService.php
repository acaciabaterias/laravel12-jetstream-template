<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalCfopCatalogEntry;
use App\Models\FiscalOperationScenario;
use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRuleMapping;
use App\Models\FiscalRulePublicationRecord;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class PlatformFiscalScenarioLookupService
{
    public function __construct(
        private readonly PlatformFiscalResolutionRules $platformFiscalResolutionRules,
        private readonly PlatformFiscalTaxProfileRules $platformFiscalTaxProfileRules,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function inspect(array $filters = []): array
    {
        $scenarios = $this->scenarioCatalog();
        $selectedScenarioKey = (string) ($filters['scenario'] ?? ($scenarios[0]['scenario_key'] ?? ''));
        $activePublication = $this->activePublication();

        return [
            'summary' => $this->summary($activePublication),
            'lookup' => $selectedScenarioKey !== '' ? $this->resolve($selectedScenarioKey, $activePublication, $filters) : null,
            'consumer_contract' => $selectedScenarioKey !== '' ? $this->consumerContract($selectedScenarioKey, $activePublication, $filters) : null,
            'scenarios' => $scenarios,
            'issues' => FiscalRuleIssueReport::query()
                ->when(
                    filled($filters['severity'] ?? null),
                    fn ($query) => $query->where('severity', $filters['severity']),
                )
                ->latest('detected_at')
                ->limit((int) ($filters['limit'] ?? 10))
                ->get(),
            'active_publication' => $activePublication,
        ];
    }

    public function activePublication(): ?FiscalRulePublicationRecord
    {
        if (! Schema::connection('central')->hasTable('fiscal_rule_publication_records')) {
            return null;
        }

        return FiscalRulePublicationRecord::query()
            ->where('status', FiscalRulePublicationStatus::Active->value)
            ->latest('published_at')
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(string $scenarioKey, ?FiscalRulePublicationRecord $publication = null, array $context = []): array
    {
        $publication ??= $this->activePublication();
        $scenario = $this->scenarioDefinition($scenarioKey);

        $mapping = $publication?->mappings()
            ->with('taxProfile')
            ->where('scenario_key', $scenarioKey)
            ->latest('id')
            ->first();

        if ($mapping instanceof FiscalRuleMapping
            && $this->platformFiscalResolutionRules->mappingMatchesScenario($scenario, $mapping, $context)
        ) {
            $catalogEntry = FiscalCfopCatalogEntry::query()
                ->where('cfop_code', $mapping->cfop_code)
                ->first();
            $resolvedContext = $this->platformFiscalTaxProfileRules->resolveContext($scenario, $context);

            return [
                'scenario_key' => $scenario['scenario_key'],
                'display_name' => $scenario['display_name'],
                'operation_direction' => $scenario['operation_direction'],
                'resolution_type' => 'active_mapping',
                'cfop_code' => $mapping->cfop_code,
                'cfop_description' => $catalogEntry?->description,
                'classification_code' => $mapping->classification_code,
                'validation_flags' => array_keys(array_filter((array) $mapping->validation_flags)),
                'tax_profile' => $mapping->taxProfile !== null
                    ? $this->platformFiscalTaxProfileRules->serialize($mapping->taxProfile, $resolvedContext)
                    : null,
                'tax_context' => $resolvedContext,
                'source_publication_id' => $publication?->id,
                'issue' => null,
            ];
        }

        return $this->platformFiscalResolutionRules->fallbackForScenario($scenario, $publication, $mapping, $context);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function consumerContract(string $scenarioKey, ?FiscalRulePublicationRecord $publication = null, array $context = []): array
    {
        $publication ??= $this->activePublication();
        $lookup = $this->resolve($scenarioKey, $publication, $context);

        return [
            'schema_version' => 'platform-fiscal-rule.v2',
            'module_consumer' => '009-fiscal-bank-orchestrator',
            'scenario_key' => $lookup['scenario_key'],
            'publication' => [
                'id' => $lookup['source_publication_id'],
                'release_key' => $publication?->release_key,
            ],
            'resolution' => [
                'type' => $lookup['resolution_type'],
                'cfop_code' => $lookup['cfop_code'],
                'classification_code' => $lookup['classification_code'],
                'validation_flags' => $lookup['validation_flags'],
            ],
            'tax_profile' => $lookup['tax_profile'] ?? null,
            'tax_context' => $lookup['tax_context'] ?? [],
            'governance' => [
                'issue' => $lookup['issue'],
                'fallback_applied' => $lookup['resolution_type'] !== 'active_mapping',
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function scenarioCatalog(): array
    {
        $configuredScenarios = collect((array) config('platform_fiscal_rules.required_scenarios', []))
            ->map(
                fn (array $definition, string $scenarioKey): array => [
                    'scenario_key' => $scenarioKey,
                    'display_name' => $definition['label'] ?? $scenarioKey,
                    'operation_direction' => $definition['operation_direction'] ?? 'export',
                    'is_required' => true,
                ]
            )
            ->keyBy('scenario_key');

        $persistedScenarios = FiscalOperationScenario::query()
            ->orderBy('display_name')
            ->get()
            ->map(
                fn (FiscalOperationScenario $scenario): array => [
                    'scenario_key' => $scenario->scenario_key,
                    'display_name' => $scenario->display_name,
                    'operation_direction' => $scenario->operation_direction,
                    'is_required' => $scenario->is_required,
                ]
            )
            ->keyBy('scenario_key');

        return $configuredScenarios
            ->merge($persistedScenarios)
            ->sortBy([
                ['is_required', 'desc'],
                ['display_name', 'asc'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function summary(?FiscalRulePublicationRecord $activePublication): array
    {
        return [
            'active_publication_id' => $activePublication?->id,
            'release_key' => $activePublication?->release_key,
            'required_scenarios' => count((array) config('platform_fiscal_rules.required_scenarios', [])),
            'covered_scenarios' => $activePublication?->mappings()->count() ?? 0,
            'open_issues' => FiscalRuleIssueReport::query()->where('resolution_status', 'open')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function scenarioDefinition(string $scenarioKey): array
    {
        $persistedScenario = FiscalOperationScenario::query()
            ->where('scenario_key', $scenarioKey)
            ->first();

        if ($persistedScenario instanceof FiscalOperationScenario) {
            return [
                'scenario_key' => $persistedScenario->scenario_key,
                'display_name' => $persistedScenario->display_name,
                'operation_direction' => $persistedScenario->operation_direction,
                'is_required' => $persistedScenario->is_required,
            ];
        }

        $configuredScenario = (array) config(sprintf('platform_fiscal_rules.required_scenarios.%s', $scenarioKey), []);

        if ($configuredScenario === []) {
            throw new InvalidArgumentException(sprintf('Unsupported fiscal scenario [%s].', $scenarioKey));
        }

        return [
            'scenario_key' => $scenarioKey,
            'display_name' => $configuredScenario['label'] ?? $scenarioKey,
            'operation_direction' => $configuredScenario['operation_direction'] ?? 'export',
            'is_required' => true,
        ];
    }
}
