<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FluxoCaixaProjetado extends Model
{
    protected $table = 'fluxo_caixa_projetados';

    protected $fillable = [
        'filial_id',
        'data_referencia',
        'saldo_inicial',
        'total_receber',
        'total_pagar',
        'saldo_projetado',
    ];

    protected function casts(): array
    {
        return [
            'data_referencia' => 'date',
            'saldo_inicial' => 'decimal:2',
            'total_receber' => 'decimal:2',
            'total_pagar' => 'decimal:2',
            'saldo_projetado' => 'decimal:2',
        ];
    }
}
