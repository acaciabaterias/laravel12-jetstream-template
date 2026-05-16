<?php

namespace Database\Seeders;

use App\Models\UsuarioPlataforma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('services.platform.super_admin_email', 'admin@bateriaexpert.com');
        $password = config('services.platform.super_admin_password');

        if (app()->environment('production') && (! $password || in_array($password, ['12345678', 'password', 'change-me-before-deploy'], true))) {
            throw new RuntimeException('Configure SUPER_ADMIN_PASSWORD com uma senha forte antes de popular o super admin em producao.');
        }

        UsuarioPlataforma::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make((string) ($password ?: 'password')),
                'papel' => 'super_admin',
                'ativo' => true,
            ]
        );
    }
}
