<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Bateria extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'sku',
        'marca',
        'tecnologia',
        'amperagem',
        'polo',
        'preco_venda',
        'atributos_dinamicos',
        'peso_sucata_kg',
        'valor_base_sucata_kg',
        'tem_logistica_reversa',
        'indice_retorno',
        'filial_id',
    ];

    protected function casts(): array
    {
        return [
            'atributos_dinamicos' => 'json',
            'preco_venda' => 'decimal:2',
            'peso_sucata_kg' => 'decimal:2',
            'valor_base_sucata_kg' => 'decimal:2',
            'tem_logistica_reversa' => 'boolean',
        ];
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function veiculos()
    {
        return $this->belongsToMany(Veiculo::class, 'aplicacoes')
            ->withPivot('observacao')
            ->withTimestamps();
    }
}
