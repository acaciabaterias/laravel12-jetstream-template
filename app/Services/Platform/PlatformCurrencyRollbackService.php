<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformCurrencyIssueReport;
use App\Models\PlatformCurrencyPublicationRecord;
use App\Support\Platform\PlatformCurrencyIssueResolutionStatus;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class PlatformCurrencyRollbackService
{
    public function __construct(
        private readonly PlatformCurrencyRollbackRules $platformCurrencyRollbackRules,
        private readonly PlatformCurrencyEventPublisher $platformCurrencyEventPublisher,
    ) {}

    public function rollback(PlatformCurrencyPublicationRecord $publication, string $reason, ?int $operatorId): PlatformCurrencyPublicationRecord
    {
        $publications = PlatformCurrencyPublicationRecord::query()
            ->whereIn('status', [
                PlatformCurrencyPublicationStatus::Active->value,
                PlatformCurrencyPublicationStatus::Superseded->value,
                PlatformCurrencyPublicationStatus::RolledBack->value,
            ])
            ->latest('published_at')
            ->latest('id')
            ->get();

        $restorablePublication = $this->platformCurrencyRollbackRules->findRestorablePublication($publications, $publication->id);

        if (! $this->platformCurrencyRollbackRules->canRollback($publication, $restorablePublication)) {
            throw new RuntimeException('Rollback indisponivel sem publicacao anterior elegivel.');
        }

        /** @var ConnectionInterface $connection */
        $connection = PlatformCurrencyPublicationRecord::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($publication, $restorablePublication, $reason, $operatorId): PlatformCurrencyPublicationRecord {
            $publication->forceFill([
                'status' => PlatformCurrencyPublicationStatus::RolledBack->value,
                'rolled_back_by' => $operatorId,
                'rolled_back_at' => now(),
                'metadata' => array_merge((array) $publication->metadata, [
                    'rollback' => [
                        'reason' => $reason,
                        'restored_publication_id' => $restorablePublication?->id,
                        'rolled_back_at' => now()->toIso8601String(),
                    ],
                ]),
            ])->save();

            $restorablePublication?->forceFill([
                'status' => PlatformCurrencyPublicationStatus::Active->value,
                'published_at' => now(),
                'metadata' => array_merge((array) $restorablePublication->metadata, [
                    'restored_from_rollback' => [
                        'publication_id' => $publication->id,
                        'reason' => $reason,
                        'restored_at' => now()->toIso8601String(),
                    ],
                ]),
            ])->save();

            PlatformCurrencyIssueReport::query()
                ->where('platform_currency_publication_record_id', $publication->id)
                ->where('resolution_status', PlatformCurrencyIssueResolutionStatus::Open->value)
                ->update([
                    'resolution_status' => PlatformCurrencyIssueResolutionStatus::RolledBack->value,
                    'resolved_at' => now(),
                    'resolved_by' => $operatorId,
                ]);

            $this->platformCurrencyEventPublisher->publish(
                'ROLLBACK_MOEDAS_PLATAFORMA_EXECUTADO',
                [
                    'publication_id' => $publication->id,
                    'release_key' => $publication->release_key,
                    'restored_publication_id' => $restorablePublication?->id,
                    'status' => PlatformCurrencyPublicationStatus::RolledBack->value,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'rolled_back_by' => $operatorId,
                        'reason' => $reason,
                        'restored_default_currency' => $restorablePublication?->default_currency_code,
                    ],
                ],
                config('platform_currencies.events.default_consumers', ['platform', 'billing', 'analytics']),
            );

            return $publication->refresh();
        });
    }
}
