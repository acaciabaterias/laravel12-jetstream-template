<?php

namespace Database\Factories;

use App\Models\CertificadoDigital;
use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CertificadoDigital>
 */
class CertificadoDigitalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cliente_id' => Cliente::factory(),
            'nome_referencia' => 'Certificado '.$this->faker->word(),
            'finalidade' => 'fiscal',
            'modelo' => 'a1',
            'formato' => 'pfx',
            'conteudo_certificado' => base64_encode('conteudo-certificado-'.$this->faker->uuid()),
            'senha_certificado' => 'senha-'.$this->faker->numerify('####'),
            'serial_numero' => strtoupper($this->faker->bothify('??##??##??##')),
            'emissor' => $this->faker->company(),
            'titular_documento' => $this->faker->numerify('##############'),
            'validade_inicio' => now()->subMonths(2)->toDateString(),
            'validade_fim' => now()->addMonths(10)->toDateString(),
            'status' => 'active',
            'prioridade' => 10,
            'metadata' => ['origem' => 'factory'],
        ];
    }
}
