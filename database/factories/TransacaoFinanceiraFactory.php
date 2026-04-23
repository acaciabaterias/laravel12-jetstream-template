<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ContaBancaria;
use App\Models\TransacaoFinanceira;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TransacaoFinanceira>
 */
class TransacaoFinanceiraFactory extends Factory
{
    protected $model = TransacaoFinanceira::class;

    public function definition(): array
    {
        $conta = ContaBancaria::query()->inRandomOrder()->first()
            ?? ContaBancaria::query()->create([
                'banco' => fake()->company(),
                'agencia' => fake()->numerify('####'),
                'conta' => fake()->numerify('#####-#'),
                'tipo' => 'corrente',
                'status' => 'ativa',
            ]);

        return [
            'conta_bancaria_id' => $conta->id,
            'tipo' => fake()->randomElement(['receita', 'despesa']),
            'valor' => fake()->randomFloat(2, 50, 5000),
            'data_transacao' => now()->subDays(random_int(0, 10)),
            'data_vencimento' => now()->addDays(random_int(0, 30))->toDateString(),
            'status' => fake()->randomElement(['pendente', 'conciliada']),
            'status_conciliado' => fake()->boolean(),
            'descricao' => fake()->sentence(),
            'identificador_externo' => 'tx-'.fake()->unique()->uuid(),
        ];
    }
}
