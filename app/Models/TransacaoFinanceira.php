<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransacaoFinanceira extends Model
{
    protected $table = 'transacoes_financeiras';

    protected $fillable = [
        'conta_id',
        'tipo',
        'categoria',
        'valor',
        'data',
        'status',
        'vale_id',
        'fornecedor_id',
        'origem',
        'observacao',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data' => 'date',
        ];
    }

    public function conta()
    {
        return $this->belongsTo(ContaBancaria::class, 'conta_id');
    }

    public function vale()
    {
        return $this->belongsTo(Vale::class);
    }

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }
}
