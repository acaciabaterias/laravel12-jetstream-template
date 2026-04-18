<?php

namespace App\Services;

use App\Models\Bateria;
use App\Models\ItemVale;
use App\Models\MargemLucroReal;
use App\Models\Filial;
use Carbon\Carbon;

class ProfitabilityAnalyzer
{
    /**
     * Recalcula e salva o snapshot da margem de lucro real para uma bateria em um período.
     */
    public function recolherDadosPeriodo(int $bateriaId, string $periodo): MargemLucroReal
    {
        $bateria = Bateria::findOrFail($bateriaId);
        $filial = $bateria->filial;

        // 1. Médias de Venda no período
        $itens = ItemVale::where('bateria_id', $bateriaId)
            ->whereHas('vale', function($q) use ($periodo) {
                $q->where('status', 'faturado')
                  ->where('created_at', 'like', "$periodo%");
            })->get();

        $vendaMedia = $itens->avg('preco_unitario_final') ?: $bateria->preco_venda;
        $quantidade = $itens->sum('quantidade');

        // 2. Custo de Aquisição (Vindo do cadastro consolidado)
        $custoAquisicao = $bateria->custo_aquisicao;

        // 3. Cálculo de Comissões (FR-FIN-03 + Feedback do Usuário)
        $comissao = 0;
        if ($filial->comissao_tipo === 'percentual') {
            $comissao = $vendaMedia * ($filial->comissao_valor / 100);
        } else {
            $comissao = $filial->comissao_valor;
        }

        // 4. Impostos e Fretes (Mocks ou Cálculos baseados no ERP)
        $impostos = $vendaMedia * 0.10; // Mock de 10% de carga tributária média
        $freteMedio = 15.00; // Mock de frete logístico médio por bateria

        // 5. Margem Final
        $margemFinal = $vendaMedia - $custoAquisicao - $impostos - $comissao - $freteMedio;

        return MargemLucroReal::updateOrCreate(
            ['bateria_id' => $bateriaId, 'periodo' => $periodo],
            [
                'valor_venda_medio' => $vendaMedia,
                'custo_aquisicao_medio' => $custoAquisicao,
                'frete_medio' => $freteMedio,
                'imposto_medio' => $impostos,
                'comissao_media' => $comissao,
                'margem_final' => $margemFinal,
            ]
        );
    }
}
