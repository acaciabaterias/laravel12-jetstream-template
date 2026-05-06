<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PapelPermissaoSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('permissoes') || ! Schema::hasTable('papel_permissao')) {
            return;
        }

        $map = [
            'dono' => [
                'gerenciar-usuarios',
                'acesso-vendas',
                'acesso-estoque',
                'acesso-logistica',
                'acesso-tecnico',
                'acesso-financeiro',
            ],
            'gestor' => [
                'gerenciar-usuarios',
                'acesso-vendas',
                'acesso-estoque',
                'acesso-logistica',
                'acesso-tecnico',
                'acesso-financeiro',
            ],
            'vendedor' => [
                'acesso-vendas',
            ],
            'tecnico' => [
                'acesso-tecnico',
            ],
            'estoquista' => [
                'acesso-estoque',
            ],
            'entregador' => [
                'acesso-logistica',
            ],
        ];

        $permissionIdsBySlug = DB::table('permissoes')
            ->pluck('id', 'slug')
            ->all();

        foreach ($map as $papel => $slugs) {
            foreach ($slugs as $slug) {
                $permissaoId = $permissionIdsBySlug[$slug] ?? null;

                if (! $permissaoId) {
                    continue;
                }

                DB::table('papel_permissao')->updateOrInsert(
                    [
                        'papel' => $papel,
                        'permissao_id' => $permissaoId,
                    ],
                    [
                        'created_at' => now(),
                    ],
                );
            }
        }
    }
}
