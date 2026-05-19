<?php

declare(strict_types=1);

namespace App\Services\Platform;

use App\Models\PlatformLocalePublicationRecord;
use App\Support\Platform\PlatformLocalePublicationStatus;
use Illuminate\Support\Collection;

class PlatformLocaleRollbackRules
{
    /**
     * @param  Collection<int, PlatformLocalePublicationRecord>  $publications
     */
    public function findRestorablePublication(Collection $publications, int $currentPublicationId): ?PlatformLocalePublicationRecord
    {
        return $publications
            ->first(fn (PlatformLocalePublicationRecord $publication): bool => $publication->id !== $currentPublicationId
                && in_array($publication->status->value, [
                    PlatformLocalePublicationStatus::Superseded->value,
                    PlatformLocalePublicationStatus::RolledBack->value,
                ], true));
    }

    public function canRollback(PlatformLocalePublicationRecord $publication, ?PlatformLocalePublicationRecord $restorablePublication): bool
    {
        return $publication->status === PlatformLocalePublicationStatus::Active
            && $restorablePublication !== null;
    }
}
