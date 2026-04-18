<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Vale extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'filial_id',
        'status',
        'observacoes',
        'data_faturamento',
    ];

    protected function casts(): array
    {
        return [
            'data_faturamento' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function itens()
    {
        return $this->hasMany(ItemVale::class);
    }

    public function pedido()
    {
        return $this->hasOne(PedidoVenda::class);
    }

    public function ordemServico()
    {
        return $this->hasOne(OrdemServico::class);
    }
}
