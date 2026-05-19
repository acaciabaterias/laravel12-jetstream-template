<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalRulePublicationRecord;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class PlatformFiscalPublicationService
{
    public function __construct(
        private readonly PlatformFiscalCoverageService $platformFiscalCoverageService,
        private readonly PlatformFiscalPublicationRules $platformFiscalPublicationRules,
        private readonly PlatformFiscalRuleEventPublisher $platformFiscalRuleEventPublisher,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $catalogEntries
     * @param  array<int, array<string, mixed>>  $scenarioMappings
     */
    public function publish(array $catalogEntries, array $scenarioMappings, ?int $operatorId): FiscalRulePublicationRecord
    {
        $coverageSnapshot = $this->platformFiscalCoverageService->snapshot($catalogEntries, $scenarioMappings);
        $validation = $this->platformFiscalPublicationRules->validate($catalogEntries, $scenarioMappings, $coverageSnapshot);

        if (! $validation['passed']) {
            throw new RuntimeException(implode(' ', $validation['messages']));
        }

        $issueReports = $this->platformFiscalCoverageService->issueReports($catalogEntries, $scenarioMappings, $coverageSnapshot);
        $status = ($coverageSnapshot['coverage_ratio'] ?? 0) >= 1.0
            ? FiscalRulePublicationStatus::Active
            : FiscalRulePublicationStatus::Draft;

        /** @var ConnectionInterface $connection */
        $connection = FiscalRulePublicationRecord::query()->getModel()->getConnection();

        return $connection->transaction(function () use (
            $catalogEntries,
            $scenarioMappings,
            $coverageSnapshot,
            $issueReports,
            $status,
            $operatorId,
        ): FiscalRulePublicationRecord {
            $activePublications = FiscalRulePublicationRecord::query()
                ->where('status', FiscalRulePublicationStatus::Active->value)
                ->get();

            $publication = FiscalRulePublicationRecord::query()->create([
                'release_key' => sprintf('fiscal-%s', now()->format('Y-m-d-His')),
                'status' => $status->value,
                'supported_scenarios' => array_values(array_unique(array_column($scenarioMappings, 'scenario_key'))),
                'catalog_snapshot' => [
                    'cfops' => array_values($catalogEntries),
                ],
                'coverage_snapshot' => $coverageSnapshot,
                'published_by' => $operatorId,
                'published_at' => now(),
                'metadata' => [
                    'open_issues' => count($issueReports),
                    'promotion_state' => $status === FiscalRulePublicationStatus::Active ? 'healthy' : 'degraded',
                ],
            ]);

            if ($status === FiscalRulePublicationStatus::Active) {
                foreach ($activePublications as $activePublication) {
                    $activePublication->forceFill([
                        'status' => FiscalRulePublicationStatus::Superseded->value,
                        'superseded_by_publication_id' => $publication->id,
                    ])->save();
                }
            }

            foreach ($scenarioMappings as $scenarioMapping) {
                $publication->mappings()->create([
                    'scenario_key' => $scenarioMapping['scenario_key'],
                    'cfop_code' => $scenarioMapping['cfop_code'],
                    'classification_code' => $scenarioMapping['classification_code'] ?? null,
                    'operation_direction' => $scenarioMapping['operation_direction'],
                    'validation_flags' => $scenarioMapping['validation_flags'] ?? [],
                    'metadata' => [
                        'source' => 'publication',
                    ],
                ]);
            }

            foreach ($issueReports as $issueReport) {
                $publication->issueReports()->create($issueReport);
            }

            $eventType = $status === FiscalRulePublicationStatus::Active
                ? 'CATALOGO_FISCAL_PUBLICADO'
                : 'CATALOGO_FISCAL_DEGRADADO_REGISTRADO';

            $this->platformFiscalRuleEventPublisher->publish(
                $eventType,
                [
                    'publication_id' => $publication->id,
                    'release_key' => $publication->release_key,
                    'supported_scenarios' => $publication->supported_scenarios,
                    'status' => $publication->status->value,
                    'coverage_ratio' => $publication->coverage_snapshot['coverage_ratio'] ?? 0,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'published_by' => $operatorId,
                        'open_issues' => $publication->issueReports()->count(),
                    ],
                ],
                config('platform_fiscal_rules.events.default_consumers', ['platform', 'fiscal', 'observability']),
            );

            return $publication->refresh();
        });
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
}
