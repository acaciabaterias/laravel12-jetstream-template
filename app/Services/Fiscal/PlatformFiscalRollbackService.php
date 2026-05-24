<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalRuleIssueReport;
use App\Models\FiscalRulePublicationRecord;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class PlatformFiscalRollbackService
{
    public function __construct(
        private readonly PlatformFiscalRollbackRules $platformFiscalRollbackRules,
        private readonly PlatformFiscalRuleEventPublisher $platformFiscalRuleEventPublisher,
    ) {}

    public function rollback(FiscalRulePublicationRecord $publication, string $reason, ?int $operatorId): FiscalRulePublicationRecord
    {
        $publications = FiscalRulePublicationRecord::query()
            ->whereIn('status', [
                FiscalRulePublicationStatus::Active->value,
                FiscalRulePublicationStatus::Superseded->value,
                FiscalRulePublicationStatus::RolledBack->value,
            ])
            ->latest('published_at')
            ->latest('id')
            ->get();

        $restorablePublication = $this->platformFiscalRollbackRules->findRestorablePublication($publications, $publication->id);
        $openCriticalIssues = FiscalRuleIssueReport::query()
            ->where('fiscal_rule_publication_record_id', $publication->id)
            ->where('resolution_status', 'open')
            ->where('severity', 'critical')
            ->count();

        if (! $this->platformFiscalRollbackRules->canRollback($publication, $restorablePublication, $openCriticalIssues)) {
            throw new RuntimeException('Rollback indisponivel sem baseline elegivel ou sem issue critica aberta.');
        }

        /** @var ConnectionInterface $connection */
        $connection = FiscalRulePublicationRecord::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($publication, $restorablePublication, $reason, $operatorId): FiscalRulePublicationRecord {
            $criticalIssueTypes = FiscalRuleIssueReport::query()
                ->where('fiscal_rule_publication_record_id', $publication->id)
                ->where('resolution_status', 'open')
                ->where('severity', 'critical')
                ->pluck('issue_type')
                ->unique()
                ->values()
                ->all();
            $materialTaxIssueCount = FiscalRuleIssueReport::query()
                ->where('fiscal_rule_publication_record_id', $publication->id)
                ->where('resolution_status', 'open')
                ->whereIn('issue_type', ['material_tax_profile_missing', 'tax_profile_gap', 'missing_interstate_rate', 'missing_cst', 'missing_csosn'])
                ->count();

            $publication->forceFill([
                'status' => FiscalRulePublicationStatus::RolledBack->value,
                'rolled_back_by' => $operatorId,
                'rolled_back_at' => now(),
                'metadata' => array_merge((array) $publication->metadata, [
                    'rollback' => [
                        'reason' => $reason,
                        'restored_publication_id' => $restorablePublication?->id,
                        'rolled_back_at' => now()->toIso8601String(),
                        'critical_issue_types' => $criticalIssueTypes,
                        'material_tax_issue_count' => $materialTaxIssueCount,
                    ],
                ]),
            ])->save();

            $restorablePublication?->forceFill([
                'status' => FiscalRulePublicationStatus::Active->value,
                'published_at' => now(),
                'metadata' => array_merge((array) $restorablePublication->metadata, [
                    'restored_from_rollback' => [
                        'publication_id' => $publication->id,
                        'reason' => $reason,
                        'restored_at' => now()->toIso8601String(),
                    ],
                ]),
            ])->save();

            FiscalRuleIssueReport::query()
                ->where('fiscal_rule_publication_record_id', $publication->id)
                ->where('resolution_status', 'open')
                ->update([
                    'resolution_status' => 'rolled_back',
                    'resolved_at' => now(),
                    'resolved_by' => $operatorId,
                ]);

            $this->platformFiscalRuleEventPublisher->publish(
                'ROLLBACK_CATALOGO_FISCAL_EXECUTADO',
                [
                    'publication_id' => $publication->id,
                    'release_key' => $publication->release_key,
                    'restored_publication_id' => $restorablePublication?->id,
                    'status' => FiscalRulePublicationStatus::RolledBack->value,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'rolled_back_by' => $operatorId,
                        'reason' => $reason,
                        'critical_issue_types' => $criticalIssueTypes,
                        'material_tax_issue_count' => $materialTaxIssueCount,
                    ],
                ],
                config('platform_fiscal_rules.events.default_consumers', ['platform', 'fiscal', 'observability']),
            );

            return $publication->refresh();
        });
    }
}
