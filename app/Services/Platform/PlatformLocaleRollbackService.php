<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformLocaleMissingKeyReport;
use App\Models\PlatformLocalePublicationRecord;
use App\Support\Platform\PlatformLocaleMissingKeyResolutionStatus;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Illuminate\Database\ConnectionInterface;
use RuntimeException;

class PlatformLocaleRollbackService
{
    public function __construct(
        private readonly PlatformLocaleRollbackRules $platformLocaleRollbackRules,
        private readonly PlatformLocalizationEventPublisher $platformLocalizationEventPublisher,
    ) {}

    public function rollback(PlatformLocalePublicationRecord $publication, string $reason, ?int $operatorId): PlatformLocalePublicationRecord
    {
        $publications = PlatformLocalePublicationRecord::query()
            ->whereIn('status', [
                PlatformLocalePublicationStatus::Active->value,
                PlatformLocalePublicationStatus::Superseded->value,
                PlatformLocalePublicationStatus::RolledBack->value,
            ])
            ->latest('published_at')
            ->latest('id')
            ->get();

        $restorablePublication = $this->platformLocaleRollbackRules->findRestorablePublication($publications, $publication->id);

        if (! $this->platformLocaleRollbackRules->canRollback($publication, $restorablePublication)) {
            throw new RuntimeException('Rollback indisponivel sem publicacao anterior elegivel.');
        }

        /** @var ConnectionInterface $connection */
        $connection = PlatformLocalePublicationRecord::query()->getModel()->getConnection();

        return $connection->transaction(function () use ($publication, $restorablePublication, $reason, $operatorId): PlatformLocalePublicationRecord {
            $publication->forceFill([
                'status' => PlatformLocalePublicationStatus::RolledBack->value,
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
                'status' => PlatformLocalePublicationStatus::Active->value,
                'published_at' => now(),
                'metadata' => array_merge((array) $restorablePublication->metadata, [
                    'restored_from_rollback' => [
                        'publication_id' => $publication->id,
                        'reason' => $reason,
                        'restored_at' => now()->toIso8601String(),
                    ],
                ]),
            ])->save();

            PlatformLocaleMissingKeyReport::query()
                ->where('platform_locale_publication_record_id', $publication->id)
                ->where('resolution_status', PlatformLocaleMissingKeyResolutionStatus::Open->value)
                ->update([
                    'resolution_status' => PlatformLocaleMissingKeyResolutionStatus::RolledBack->value,
                    'resolved_at' => now(),
                    'resolved_by' => $operatorId,
                ]);

            $this->platformLocalizationEventPublisher->publish(
                'ROLLBACK_LOCALIZACAO_PLATAFORMA_EXECUTADO',
                [
                    'publication_id' => $publication->id,
                    'release_key' => $publication->release_key,
                    'restored_publication_id' => $restorablePublication?->id,
                    'status' => PlatformLocalePublicationStatus::RolledBack->value,
                    'occurred_at' => now()->toIso8601String(),
                    'metadata' => [
                        'rolled_back_by' => $operatorId,
                        'reason' => $reason,
                        'restored_default_locale' => $restorablePublication?->default_locale,
                    ],
                ],
                config('platform_localization.events.default_consumers', ['platform', 'observability']),
            );

            return $publication->refresh();
        });
    }
}
