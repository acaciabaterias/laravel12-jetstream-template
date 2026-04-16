<?php

namespace Database\Seeders;

use App\Models\Filial;
use Illuminate\Database\Seeder;

class FilialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Filial::create([
            'nome' => 'Matriz',
            'cnpj' => '12.345.678/0001-99',
            'active' => true,
        ]);
    }
}
