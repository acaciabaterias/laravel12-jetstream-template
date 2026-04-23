<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndiceRetornoProduto extends Model
{
    use Auditable, HasFactory;

    protected $table = 'indices_retorno_produto';

    protected $fillable = [
        'bateria_id',
        'periodo_inicio',
        'periodo_fim',
        'total_vendidas',
        'total_garantias',
        'indice_calculado',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio' => 'date',
            'periodo_fim' => 'date',
            'indice_calculado' => 'decimal:4',
        ];
    }

    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class);
    }
}
