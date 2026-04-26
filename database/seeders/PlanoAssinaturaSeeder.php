<?php

namespace Database\Seeders;

use App\Models\PlanoAssinatura;
use Illuminate\Database\Seeder;

class PlanoAssinaturaSeeder extends Seeder
{
    public function run()
    {
        $planos = [
            [
                'nome' => 'Essential',
                'slug' => 'essential',
                'preco_mensal' => 147.00,
                'max_usuarios' => 3,
                'max_estoque_itens' => 500,
                'has_white_label' => false,
                'has_api_integration' => false,
                'has_support_priority' => false,
            ],
            [
                'nome' => 'Pro',
                'slug' => 'pro',
                'preco_mensal' => 297.00,
                'max_usuarios' => 10,
                'max_estoque_itens' => 2000,
                'has_white_label' => true,
                'has_api_integration' => false,
                'has_support_priority' => true,
            ],
            [
                'nome' => 'Enterprise',
                'slug' => 'enterprise',
                'preco_mensal' => 597.00,
                'max_usuarios' => 999,
                'max_estoque_itens' => 999999,
                'has_white_label' => true,
                'has_api_integration' => true,
                'has_support_priority' => true,
            ],
        ];

        foreach ($planos as $plano) {
            PlanoAssinatura::updateOrCreate(['slug' => $plano['slug']], $plano);
        }
    }
}
