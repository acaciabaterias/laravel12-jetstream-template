<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ConciliacaoPagamentoSaaS;
use App\Models\CobrancaSaaSExterna;
use App\Models\FaturaSaaS;
use App\Models\RetornoPagamentoSaaS;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConciliacaoPagamentoSaaS>
 */
class ConciliacaoPagamentoSaaSFactory extends Factory
{
    public function definition(): array
    {
        return [
            'fatura_saas_id' => FaturaSaaS::factory(),
            'cobranca_saas_externa_id' => CobrancaSaaSExterna::factory(),
            'retorno_pagamento_saas_id' => RetornoPagamentoSaaS::factory(),
            'status' => 'matched',
            'reconciliation_type' => 'automatic',
            'expected_amount' => 250.00,
            'received_amount' => 250.00,
            'difference_amount' => 0,
            'reconciled_at' => now(),
            'operator_user_id' => UsuarioPlataforma::factory()->billing(),
            'notes' => null,
            'metadata' => ['source' => 'factory'],
        ];
    }
}
