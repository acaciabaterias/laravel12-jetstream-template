<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueMovimentacao extends Model
{
    use Auditable, HasFactory;

    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'bateria_id',
        'deposito_id',
        'user_id',
        'tipo_operacao',
        'origem',
        'quantidade',
        'justificativa',
        'data_movimentacao',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'integer',
            'data_movimentacao' => 'datetime',
        ];
    }

    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class);
    }

    public function deposito(): BelongsTo
    {
        return $this->belongsTo(Deposito::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
