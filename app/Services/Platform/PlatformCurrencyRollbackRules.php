<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformCurrencyPublicationRecord;
use App\Support\Platform\PlatformCurrencyPublicationStatus;
use Illuminate\Support\Collection;

class PlatformCurrencyRollbackRules
{
    /**
     * @param  Collection<int, PlatformCurrencyPublicationRecord>  $publications
     */
    public function findRestorablePublication(Collection $publications, int $currentPublicationId): ?PlatformCurrencyPublicationRecord
    {
        return $publications
            ->first(fn (PlatformCurrencyPublicationRecord $publication): bool => $publication->id !== $currentPublicationId
                && in_array($publication->status, [
                    PlatformCurrencyPublicationStatus::Superseded,
                    PlatformCurrencyPublicationStatus::RolledBack,
                ], true));
    }

    public function canRollback(PlatformCurrencyPublicationRecord $publication, ?PlatformCurrencyPublicationRecord $restorablePublication): bool
    {
        return $publication->status === PlatformCurrencyPublicationStatus::Active
            && $restorablePublication !== null;
    }
}
