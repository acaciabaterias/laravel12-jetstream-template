<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([MultiTenantScope::class])]
#[ObservedBy([\App\Observers\EstoqueMovimentacaoObserver::class])]
class EstoqueMovimentacao extends Model
{
    use HasFactory;

    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'bateria_id',
        'filial_id',
        'deposito_id',
        'user_id',
        'tipo',
        'quantidade',
        'origem',
        'referencia',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'datetime',
            'quantidade' => 'integer',
        ];
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function deposito()
    {
        return $this->belongsTo(Deposito::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
