<?php

namespace App\Services\Contracts\Integration;

use App\Models\EntregaIntegracao;
use App\Models\User;

interface IntegrationReplayServiceContract
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function replay(EntregaIntegracao $delivery, User $operator, array $context = []): EntregaIntegracao;
}
