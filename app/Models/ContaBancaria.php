<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContaBancaria extends Model
{
    protected $table = 'contas_bancarias';

    protected $fillable = [
        'filial_id',
        'banco',
        'agencia',
        'conta',
        'tipo',
        'token_api',
        'status',
    ];

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function transacoes()
    {
        return $this->hasMany(TransacaoFinanceira::class, 'conta_id');
    }
}
