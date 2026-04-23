<?php

namespace Database\Seeders;

use App\Models\UsuarioPlataforma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        UsuarioPlataforma::query()->updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@bateriaexpert.com')],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', '12345678')),
                'papel' => 'super_admin',
                'ativo' => true,
            ]
        );
    }
}
