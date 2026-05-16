<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bateria extends Model
{
    use Auditable, HasFactory, SoftDeletes;

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

    public function veiculos(): BelongsToMany
    {
        return $this->belongsToMany(Veiculo::class, 'aplicacoes')
            ->withPivot('observacao')
            ->withTimestamps();
    }
}
