<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bateria;
use App\Models\ItemVale;
use App\Models\Vale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemVale>
 */
class ItemValeFactory extends Factory
{
    protected $model = ItemVale::class;

    public function definition(): array
    {
        $bateria = Bateria::query()->inRandomOrder()->first()
            ?? Bateria::query()->create([
                'sku' => 'BAT-'.fake()->unique()->numerify('####'),
                'marca' => fake()->company(),
                'preco_venda' => fake()->randomFloat(2, 50, 2000),
                'peso_sucata_kg' => fake()->randomFloat(2, 1, 20),
                'valor_base_sucata_kg' => fake()->randomFloat(2, 1, 10),
            ]);

        $precoUnitario = fake()->randomFloat(2, 50, 2000);
        $devolveuSucata = fake()->boolean();
        $precoFinal = $devolveuSucata
            ? $precoUnitario
            : round($precoUnitario + ((float) $bateria->peso_sucata_kg * (float) $bateria->valor_base_sucata_kg), 2);

        return [
            'vale_id' => Vale::query()->inRandomOrder()->value('id') ?? Vale::factory(),
            'bateria_id' => $bateria->id,
            'quantidade' => fake()->numberBetween(1, 100),
            'preco_unitario_original' => $precoUnitario,
            'preco_unitario_final' => $precoFinal,
            'flag_devolveu_sucata' => $devolveuSucata,
            'observacao' => fake()->optional()->sentence(),
        ];
    }
}
