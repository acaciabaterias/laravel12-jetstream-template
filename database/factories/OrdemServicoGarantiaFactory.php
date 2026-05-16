<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bateria;
use App\Models\Cliente;
use App\Models\OrdemServicoGarantia;
use App\Models\Vale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrdemServicoGarantia>
 */
class OrdemServicoGarantiaFactory extends Factory
{
    protected $model = OrdemServicoGarantia::class;

    public function definition(): array
    {
        $resultado = fake()->randomElement(['procedente', 'improcedente']);

        return [
            'cliente_id' => Cliente::query()->inRandomOrder()->value('id') ?? Cliente::factory(),
            'bateria_id' => Bateria::query()->inRandomOrder()->value('id') ?? Bateria::query()->create([
                'sku' => 'GAR-'.fake()->unique()->numerify('####'),
                'marca' => fake()->company(),
                'preco_venda' => fake()->randomFloat(2, 100, 800),
            ])->id,
            'vale_original_id' => Vale::query()->inRandomOrder()->value('id'),
            'data_abertura' => now()->subDays(random_int(0, 60)),
            'status' => fake()->randomElement(['aberta', 'em_analise', 'procedente', 'improcedente']),
            'laudo' => fake()->paragraph(),
            'resultado' => $resultado,
            'cobranca_valor' => $resultado === 'improcedente' ? fake()->randomFloat(2, 50, 500) : null,
        ];
    }
}
