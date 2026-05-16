<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PoliticaRecuperacaoReceita;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PoliticaRecuperacaoReceita>
 */
class PoliticaRecuperacaoReceitaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->unique()->words(3, true),
            'slug' => fake()->unique()->slug(3),
            'status' => 'active',
            'entry_conditions' => ['invoice_overdue_days' => 1],
            'stage_definitions' => [
                ['name' => 'd1', 'channel' => 'email', 'delay_hours' => 0],
                ['name' => 'd3', 'channel' => 'whatsapp', 'delay_hours' => 48],
            ],
            'escalation_rules' => ['days_overdue' => 7, 'severity' => 'high'],
            'reengagement_rules' => ['enabled' => true, 'delay_days' => 3],
            'metadata' => ['source' => 'factory'],
        ];
    }
}
