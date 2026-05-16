<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtratoCapturaLog extends Model
{
    protected $table = 'extrato_captura_logs';

    protected $fillable = [
        'consentimento_id',
        'status',
        'total_transacoes',
        'periodo_de',
        'periodo_ate',
        'duracao_ms',
        'erro_descricao',
    ];

    protected function casts(): array
    {
        return [
            'periodo_de' => 'datetime',
            'periodo_ate' => 'datetime',
            'duracao_ms' => 'integer',
        ];
    }

    public function consentimento(): BelongsTo
    {
        return $this->belongsTo(Consentimento::class, 'consentimento_id');
    }
}
