<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MargemLucroReal extends Model
{
    protected $table = 'margem_lucro_reais';

    protected $fillable = [
        'bateria_id',
        'periodo',
        'valor_venda_medio',
        'custo_aquisicao_medio',
        'frete_medio',
        'imposto_medio',
        'comissao_media',
        'margem_final',
    ];

    protected function casts(): array
    {
        return [
            'valor_venda_medio' => 'decimal:2',
            'custo_aquisicao_medio' => 'decimal:2',
            'frete_medio' => 'decimal:2',
            'imposto_medio' => 'decimal:2',
            'comissao_media' => 'decimal:2',
            'margem_final' => 'decimal:2',
        ];
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }
}
