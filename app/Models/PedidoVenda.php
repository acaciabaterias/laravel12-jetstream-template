<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoVenda extends Model
{
    use Auditable, HasFactory;

    protected $table = 'pedidos_venda';

    protected $fillable = [
        'vale_id',
        'cliente_id',
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

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
