<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FiscalCfopCatalogEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FiscalCfopCatalogEntry>
 */
class FiscalCfopCatalogEntryFactory extends Factory
{
    protected $model = FiscalCfopCatalogEntry::class;

    public function definition(): array
    {
        return [
            'cfop_code' => fake()->unique()->randomElement(['7101', '3101', '7102', '3551']),
            'description' => fake()->randomElement([
                'Venda de producao do estabelecimento para o exterior',
                'Compra para industrializacao do exterior',
                'Venda de mercadoria adquirida de terceiros para o exterior',
                'Compra de bem para o ativo imobilizado do exterior',
            ]),
            'operation_direction' => fake()->randomElement(['export', 'import']),
            'is_enabled' => true,
            'metadata' => ['source' => 'test'],
        ];
    }
}
