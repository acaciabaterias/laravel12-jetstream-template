<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConciliacaoPendente extends Model
{
    use Auditable, HasFactory;

    protected $table = 'conciliacoes_pendentes';

    protected $fillable = [
        'transacao_financeira_id',
        'motivo',
        'payload_bancario',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'payload_bancario' => 'json',
        ];
    }

    public function transacaoFinanceira(): BelongsTo
    {
        return $this->belongsTo(TransacaoFinanceira::class);
    }
}
