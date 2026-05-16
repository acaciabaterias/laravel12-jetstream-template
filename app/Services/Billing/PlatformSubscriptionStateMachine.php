<?php

namespace App\Services\Billing;

class PlatformSubscriptionStateMachine
{
    /**
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_TRANSITIONS = [
        'draft' => ['trial', 'active'],
        'trial' => ['active', 'cancelled', 'expired'],
        'active' => ['grace_period', 'blocked', 'cancelled', 'expired'],
        'grace_period' => ['active', 'blocked', 'cancelled'],
        'blocked' => ['active', 'cancelled'],
        'expired' => [],
        'cancelled' => [],
    ];

    public function canTransition(?string $from, string $to): bool
    {
        if ($from === null) {
            return in_array($to, ['trial', 'active'], true);
        }

        return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
    }
}
