<?php

declare(strict_types=1);

namespace App\Services\Fiscal;

use App\Models\FiscalRulePublicationRecord;
use App\Support\Fiscal\FiscalRulePublicationStatus;
use Illuminate\Support\Collection;

class PlatformFiscalRollbackRules
{
    /**
     * @param  Collection<int, FiscalRulePublicationRecord>  $publications
     */
    public function findRestorablePublication(Collection $publications, int $publicationId): ?FiscalRulePublicationRecord
    {
        return $publications
            ->first(function (FiscalRulePublicationRecord $publication) use ($publicationId): bool {
                return $publication->id !== $publicationId
                    && in_array($publication->status->value, [
                        FiscalRulePublicationStatus::Superseded->value,
                        FiscalRulePublicationStatus::RolledBack->value,
                    ], true);
            });
    }

    public function canRollback(
        FiscalRulePublicationRecord $publication,
        ?FiscalRulePublicationRecord $restorablePublication,
        int $openCriticalIssues,
    ): bool {
        return $publication->status->value === FiscalRulePublicationStatus::Active->value
            && $restorablePublication instanceof FiscalRulePublicationRecord
            && $openCriticalIssues > 0;
    }
}
