<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExcecaoConciliacaoSaaS;
use App\Models\CobrancaSaaSExterna;
use App\Models\ConciliacaoPagamentoSaaS;
use App\Models\FaturaSaaS;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExcecaoConciliacaoSaaS>
 */
class ExcecaoConciliacaoSaaSFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fatura_saas_id' => FaturaSaaS::factory(),
            'cobranca_saas_externa_id' => CobrancaSaaSExterna::factory(),
            'retorno_pagamento_saas_id' => RetornoPagamentoSaaS::factory(),
            'conciliacao_pagamento_saas_id' => ConciliacaoPagamentoSaaS::factory(),
            'status' => 'open',
            'exception_type' => 'amount_mismatch',
            'severity' => 'high',
            'impact_on_subscription' => 'review_block',
            'opened_at' => now(),
            'resolved_at' => null,
            'owner_user_id' => UsuarioPlataforma::factory()->billing(),
            'resolution_notes' => null,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
