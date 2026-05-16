<?php

namespace Tests\Unit;

use App\Models\EntregaIntegracao;
use App\Models\User;
use App\Policies\IntegrationBackbonePolicy;
use App\Support\Integration\IntegrationDirection;
use App\Support\Integration\IntegrationFlowStatus;
use App\Support\Integration\IntegrationTransportKind;
use PHPUnit\Framework\TestCase;

class IntegrationReplayPolicyTest extends TestCase
{
    public function test_gestor_can_replay_integration_delivery(): void
    {
        $policy = new IntegrationBackbonePolicy;
        $user = new User(['papel' => 'gestor', 'ativo' => true]);
        $delivery = new EntregaIntegracao([
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'status' => IntegrationFlowStatus::DeadLetter,
        ]);

        $this->assertTrue($policy->replay($user, $delivery));
    }

    public function test_vendedor_cannot_replay_integration_delivery(): void
    {
        $policy = new IntegrationBackbonePolicy;
        $user = new User(['papel' => 'vendedor', 'ativo' => true]);
        $delivery = new EntregaIntegracao([
            'direction' => IntegrationDirection::Outbound,
            'transport_kind' => IntegrationTransportKind::Broker,
            'status' => IntegrationFlowStatus::DeadLetter,
        ]);

        $this->assertFalse($policy->replay($user, $delivery));
    }
}
