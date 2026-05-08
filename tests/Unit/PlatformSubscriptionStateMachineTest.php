<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\Billing\PlatformSubscriptionStateMachine;
use PHPUnit\Framework\TestCase;

class PlatformSubscriptionStateMachineTest extends TestCase
{
    public function test_it_allows_valid_platform_subscription_transitions(): void
    {
        $stateMachine = new PlatformSubscriptionStateMachine;

        $this->assertTrue($stateMachine->canTransition(null, 'active'));
        $this->assertTrue($stateMachine->canTransition('active', 'grace_period'));
        $this->assertTrue($stateMachine->canTransition('grace_period', 'blocked'));
        $this->assertTrue($stateMachine->canTransition('blocked', 'active'));
        $this->assertTrue($stateMachine->canTransition('active', 'cancelled'));
    }

    public function test_it_rejects_invalid_platform_subscription_transitions(): void
    {
        $stateMachine = new PlatformSubscriptionStateMachine;

        $this->assertFalse($stateMachine->canTransition(null, 'blocked'));
        $this->assertFalse($stateMachine->canTransition('cancelled', 'active'));
        $this->assertFalse($stateMachine->canTransition('expired', 'active'));
        $this->assertFalse($stateMachine->canTransition('trial', 'blocked'));
    }
}
