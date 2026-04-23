<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MargemLucroReal extends Model
{
    use Auditable, HasFactory;

    protected $table = 'margens_lucro_real';

    protected $fillable = [
        'bateria_id',
        'periodo_inicio',
        'periodo_fim',
        'valor_venda',
        'custo_aquisicao',
        'frete',
        'imposto',
        'comissao',
        'margem_calculada',
    ];

    protected function casts(): array
    {
        return [
            'periodo_inicio' => 'date',
            'periodo_fim' => 'date',
            'valor_venda' => 'decimal:2',
            'custo_aquisicao' => 'decimal:2',
            'frete' => 'decimal:2',
            'imposto' => 'decimal:2',
            'comissao' => 'decimal:2',
            'margem_calculada' => 'decimal:4',
        ];
    }

    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class);
    }
}
