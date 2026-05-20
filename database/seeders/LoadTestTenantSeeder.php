<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LoadTestTenantSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            $subdomain = sprintf('loadtest-%03d', $i);
            $tenant = Cliente::query()->firstOrNew([
                'subdominio' => $subdomain,
            ]);

            $tenant->fill([
                'cnpj' => str_pad((string) $i, 14, '0', STR_PAD_LEFT),
                'razao_social' => sprintf('Tenant de Carga %03d', $i),
                'nome_fantasia' => sprintf('Carga %03d', $i),
                'email_contato' => sprintf('loadtest-%03d@bateriaexpert.test', $i),
                'telefone' => sprintf('1199%06d', $i),
                'endereco' => sprintf('Rua de Carga %03d', $i),
                'saldo_sucata_kg' => 0,
                'plano' => 'enterprise',
                'status' => 'active',
                'supabase_project_ref' => Str::padLeft((string) $i, 20, '0'),
                'supabase_url' => sprintf('https://%s.%s', $subdomain, 'erp.local'),
                'supabase_db_host' => 'tenant-shared-loadtest.pgsql.local',
            ]);

            $expectedPassword = 'loadtest-shared-password';
            $expectedAnonKey = hash('sha256', sprintf('loadtest-anon-%03d', $i));
            $expectedServiceRoleKey = hash('sha256', sprintf('loadtest-service-%03d', $i));

            if ($tenant->supabase_db_password !== $expectedPassword) {
                $tenant->supabase_db_password = $expectedPassword;
            }

            if ($tenant->supabase_anon_key !== $expectedAnonKey) {
                $tenant->supabase_anon_key = $expectedAnonKey;
            }

            if ($tenant->supabase_service_role_key !== $expectedServiceRoleKey) {
                $tenant->supabase_service_role_key = $expectedServiceRoleKey;
            }

            $tenant->save();
        }
    }
}
