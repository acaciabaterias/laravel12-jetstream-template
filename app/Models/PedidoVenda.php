<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class PedidoVenda extends Model
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'vale_id',
        'cliente_id',
        'filial_id',
        'data_emissao',
        'valor_total',
        'status',
        'nf_referencia',
    ];

    protected function casts(): array
    {
        return [
            'data_emissao' => 'datetime',
            'valor_total' => 'decimal:2',
        ];
    }

    public function vale()
    {
        return $this->belongsTo(Vale::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
