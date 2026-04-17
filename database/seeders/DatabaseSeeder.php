<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->withPersonalTeam()->create();

        User::factory()->withPersonalTeam()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Criar Super Admin da Plataforma (Central)
        if (\App\Models\UsuarioPlataforma::count() === 0) {
            \App\Models\UsuarioPlataforma::create([
                'nome' => 'Super Admin',
                'email' => 'admin@bateriaexpert.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'papel' => 'super_admin',
            ]);
        }
    }
}
