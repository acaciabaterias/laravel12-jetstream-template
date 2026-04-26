<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class LoadTestTenantSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            Cliente::factory()->create([
                'subdominio' => "test{$i}.local",
                'status' => 'active'
            ]);
        }
    }
}
