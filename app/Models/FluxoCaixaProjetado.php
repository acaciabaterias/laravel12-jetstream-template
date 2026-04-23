<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FluxoCaixaProjetado extends Model
{
    use Auditable, HasFactory;

    protected $table = 'fluxos_caixa_projetado';

    protected $fillable = [
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
