<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContaBancaria extends Model
{
    use Auditable, HasFactory;

    protected $table = 'contas_bancarias';

    protected $fillable = [
        'banco',
        'agencia',
        'conta',
        'tipo',
        'token_api',
        'status',
    ];

    public function transacoes(): HasMany
    {
        return $this->hasMany(TransacaoFinanceira::class, 'conta_bancaria_id');
    }
}
