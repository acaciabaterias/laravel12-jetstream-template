<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    public function definition(): array
    {
        return [
            'cnpj' => $this->faker->unique()->numerify('##.###.###/0001-##'),
            'razao_social' => $this->faker->company(),
            'nome_fantasia' => $this->faker->company(),
            'email_contato' => $this->faker->companyEmail(),
            'telefone' => $this->faker->phoneNumber(),
            'subdominio' => $this->faker->unique()->slug(1),
            'plano' => 'essential',
            'status' => 'active',
            'supabase_project_ref' => Str::random(20),
            'supabase_url' => 'https://'.Str::random(10).'.supabase.co',
            'supabase_db_host' => 'db.'.Str::random(10).'.supabase.co',
            'supabase_db_password' => Str::random(16),
            'supabase_anon_key' => Str::random(100),
            'supabase_service_role_key' => Str::random(100),
        ];
    }
}
