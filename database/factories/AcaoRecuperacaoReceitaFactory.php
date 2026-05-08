<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AcaoRecuperacaoReceita;
use App\Models\CasoRecuperacaoReceita;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcaoRecuperacaoReceita>
 */
class AcaoRecuperacaoReceitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'caso_recuperacao_receita_id' => CasoRecuperacaoReceita::factory(),
            'action_type' => 'automated_reminder',
            'channel' => 'email',
            'stage_name' => 'd1',
            'status' => 'scheduled',
            'idempotency_key' => fake()->unique()->sha1(),
            'scheduled_for' => now()->addHour(),
            'executed_at' => null,
            'result_code' => null,
            'operator_user_id' => UsuarioPlataforma::factory()->billing(),
            'payload_snapshot' => ['template' => 'invoice_overdue_d1'],
            'metadata' => ['source' => 'factory'],
        ];
    }
}
