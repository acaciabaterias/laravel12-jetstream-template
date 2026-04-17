<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run()
    {
        // First check if the table exists (important for multi-db setup)
        if (! DB::connection('tenant')->getSchemaBuilder()->hasTable('users')) {
            return;
        }

        User::updateOrCreate(
            ['email' => env('SUPER_ADMIN_EMAIL', 'admin@bateriaexpert.com')],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make(env('SUPER_ADMIN_PASSWORD', '12345678')),
                'papel' => 'super_admin',
                'filial_id' => null,
                'ativo' => true,
            ]
        );
    }
}
