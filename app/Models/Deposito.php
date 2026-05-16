<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deposito extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'nome',
        'tipo',
        'status',
    ];

    public function movimentacoes(): HasMany
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }

    public function saldos(): HasMany
    {
        return $this->hasMany(EstoqueSaldo::class);
    }
}
