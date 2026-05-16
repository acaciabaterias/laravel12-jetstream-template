<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PermissoesSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('permissoes')) {
            return;
        }

        $permissoes = [
            ['nome' => 'Gerenciar usuários', 'slug' => 'gerenciar-usuarios', 'descricao' => 'Controla criação, edição e ativação de usuários.'],
            ['nome' => 'Acesso vendas', 'slug' => 'acesso-vendas', 'descricao' => 'Libera fluxo comercial de vales, pedidos e OS.'],
            ['nome' => 'Acesso estoque', 'slug' => 'acesso-estoque', 'descricao' => 'Libera ajustes, XML e conta sucata.'],
            ['nome' => 'Acesso logística', 'slug' => 'acesso-logistica', 'descricao' => 'Libera roteirização e entregas.'],
            ['nome' => 'Acesso técnico', 'slug' => 'acesso-tecnico', 'descricao' => 'Libera garantias, laudos e empréstimos.'],
            ['nome' => 'Acesso financeiro', 'slug' => 'acesso-financeiro', 'descricao' => 'Libera conciliação, fluxo de caixa e orquestração fiscal/bancária.'],
        ];

        foreach ($permissoes as $permissao) {
            DB::table('permissoes')->updateOrInsert(
                ['slug' => $permissao['slug']],
                $permissao,
            );
        }
    }
}
