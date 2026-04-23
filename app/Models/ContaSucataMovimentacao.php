<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaSucataMovimentacao extends Model
{
    use Auditable, HasFactory;

    protected $table = 'conta_sucata_movimentacoes';

    protected $fillable = [
        'entidade_tipo',
        'entidade_id',
        'tipo_movimento',
        'quantidade_kg',
        'valor_unitario',
        'saldo_resultante',
        'origem',
    ];

    protected function casts(): array
    {
        return [
            'quantidade_kg' => 'decimal:2',
            'valor_unitario' => 'decimal:2',
            'saldo_resultante' => 'decimal:2',
        ];
    }
}
