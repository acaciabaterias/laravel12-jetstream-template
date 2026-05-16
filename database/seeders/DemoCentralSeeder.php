<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\PlanoAssinatura;
use App\Models\UsuarioPlataforma;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DemoCentralSeeder extends Seeder
{
    public function run(): void
    {
        if (Schema::hasTable('planos')) {
            $this->call(PlanosSeeder::class);
        }

        if (Schema::hasTable('usuarios_plataforma')) {
            UsuarioPlataforma::query()->updateOrCreate(
                ['email' => 'demo.superadmin@bateriaexpert.test'],
                [
                    'name' => 'Demo Super Admin',
                    'password' => 'password',
                    'papel' => 'super_admin',
                    'ativo' => true,
                ],
            );
        }

        if (Schema::hasTable('clientes')) {
            $planSlug = Schema::hasTable('planos')
                ? (PlanoAssinatura::query()->where('slug', 'pro')->value('slug') ?? 'pro')
                : 'pro';

            Cliente::query()->updateOrCreate(
                ['subdominio' => 'demo-central'],
                [
                    'cnpj' => '12345678000195',
                    'razao_social' => 'BateriaExpert Demo Central',
                    'nome_fantasia' => 'Demo Central',
                    'email_contato' => 'central@bateriaexpert.test',
                    'telefone' => '11999998888',
                    'endereco' => 'Rua Central, 100',
                    'saldo_sucata_kg' => 250,
                    'plano' => $planSlug,
                    'status' => 'active',
                    'trial_ends_at' => null,
                    'subscription_ends_at' => now()->addMonths(3),
                    'supabase_project_ref' => 'demo'.Str::lower(Str::random(12)),
                    'supabase_url' => 'https://demo-central.supabase.co',
                    'supabase_db_host' => 'db.demo-central.supabase.co',
                    'supabase_db_password' => Str::random(20),
                    'supabase_anon_key' => Str::random(80),
                    'supabase_service_role_key' => Str::random(80),
                ],
            );
        }
    }
}
