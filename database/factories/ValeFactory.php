<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\ItemVale;
use App\Models\User;
use App\Models\Vale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vale>
 */
class ValeFactory extends Factory
{
    protected $model = Vale::class;

    public function definition(): array
    {
        $seller = User::query()->inRandomOrder()->first()
            ?? User::factory()->create(['papel' => 'vendedor', 'ativo' => true]);
        $cliente = Cliente::query()->inRandomOrder()->first() ?? Cliente::factory()->create();

        return [
            'cliente_id' => $cliente->id,
            'vendedor_id' => $seller->id,
            'status' => fake()->randomElement(['aberto', 'faturado', 'cancelado']),
            'data_criacao' => now()->subDays(random_int(0, 30)),
            'data_faturamento' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'observacoes' => fake()->sentence(),
            'created_by' => $seller->id,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Vale $vale): void {
            ItemVale::factory()
                ->count(random_int(1, 5))
                ->for($vale)
                ->create();
        });
    }
}
