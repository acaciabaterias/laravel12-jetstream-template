<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class DemoSqlScriptsTest extends TestCase
{
    public function test_demo_sql_scripts_exist(): void
    {
        $paths = [
            database_path('schema/demo_data_central.sql'),
            database_path('schema/demo_data_tenant.sql'),
            database_path('schema/demo_data_financeiro.sql'),
        ];

        foreach ($paths as $path) {
            $this->assertFileExists($path);
        }
    }

    public function test_demo_sql_scripts_cover_expected_tables(): void
    {
        $central = file_get_contents(database_path('schema/demo_data_central.sql'));
        $tenant = file_get_contents(database_path('schema/demo_data_tenant.sql'));
        $financeiro = file_get_contents(database_path('schema/demo_data_financeiro.sql'));

        $this->assertIsString($central);
        $this->assertIsString($tenant);
        $this->assertIsString($financeiro);

        $this->assertStringContainsString('insert into public.planos', $central);
        $this->assertStringContainsString('insert into public.usuarios_plataforma', $central);
        $this->assertStringContainsString('insert into public.clientes', $central);

        $this->assertStringContainsString('insert into public.clientes', $tenant);
        $this->assertStringContainsString('insert into public.veiculos', $tenant);
        $this->assertStringContainsString('insert into public.baterias', $tenant);
        $this->assertStringContainsString('insert into public.vales', $tenant);
        $this->assertStringContainsString('insert into public.ordens_servico', $tenant);

        $this->assertStringContainsString('insert into public.transacoes_financeiras', $financeiro);
        $this->assertStringContainsString('insert into public.boletos_orquestrados', $financeiro);
        $this->assertStringContainsString('insert into public.notas_fiscais_orquestradas', $financeiro);
    }
}
