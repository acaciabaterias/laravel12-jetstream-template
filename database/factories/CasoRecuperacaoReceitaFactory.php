<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AssinaturaPlataforma;
use App\Models\CasoRecuperacaoReceita;
use App\Models\Cliente;
use App\Models\FaturaSaaS;
use App\Models\PoliticaRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CasoRecuperacaoReceita>
 */
class CasoRecuperacaoReceitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'assinatura_id' => AssinaturaPlataforma::factory(),
            'fatura_saas_id' => FaturaSaaS::factory(),
            'politica_recuperacao_receita_id' => PoliticaRecuperacaoReceita::factory(),
            'status' => 'open',
            'entry_reason' => 'invoice_overdue',
            'current_stage' => 'd1',
            'severity' => 'medium',
            'opened_at' => now(),
            'closed_at' => null,
            'owner_user_id' => UsuarioPlataforma::factory()->billing(),
            'last_action_at' => now(),
            'metadata' => ['source' => 'factory'],
        ];
    }
}
