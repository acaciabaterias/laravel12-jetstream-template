<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransacaoBancaria extends Model
{
    protected $table = 'transacao_bancarias';

    protected $fillable = [
        'consentimento_id',
        'tx_id_original',
        'data_lancamento',
        'data_valor',
        'descricao',
        'valor',
        'tipo',
        'categoria',
        'conta_origem',
        'conta_destino',
        'deduplicacao_hash',
    ];

    public static function generateHash(array $data): string
    {
        // FR-004-04: data + valor + descricao + conta (consentimento)
        return hash('sha256', $data['data'].$data['valor'].$data['descricao'].($data['consentimento_id'] ?? ''));
    }

    protected function casts(): array
    {
        return [
            'data_lancamento' => 'date',
            'valor' => 'decimal:2',
        ];
    }

    public function consentimento(): BelongsTo
    {
        return $this->belongsTo(Consentimento::class, 'consentimento_id');
    }
}
