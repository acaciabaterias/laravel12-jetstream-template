<?php

namespace App\Services\Integration;

use App\Models\EventoInbox;

class InboxDeduplicator
{
    public function findDuplicate(
        string $tenantExternalRef,
        string $externalEventId,
        string $idempotencyKey
    ): ?EventoInbox {
        return EventoInbox::query()
            ->where('tenant_external_ref', $tenantExternalRef)
            ->where(function ($query) use ($externalEventId, $idempotencyKey): void {
                $query->where('external_event_id', $externalEventId)
                    ->orWhere('idempotency_key', $idempotencyKey);
            })
            ->first();
    }

    public function isDuplicate(
        string $tenantExternalRef,
        string $externalEventId,
        string $idempotencyKey
    ): bool {
        return $this->findDuplicate($tenantExternalRef, $externalEventId, $idempotencyKey) !== null;
    }
}
