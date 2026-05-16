<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TransacaoFinanceira extends Model
{
    use Auditable, HasFactory;

    protected $table = 'transacoes_financeiras';

    protected $fillable = [
        'conta_bancaria_id',
        'tipo',
        'valor',
        'data_transacao',
        'data_vencimento',
        'status',
        'status_conciliado',
        'origem_tipo',
        'origem_id',
        'descricao',
        'identificador_externo',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_transacao' => 'datetime',
            'data_vencimento' => 'date',
            'status_conciliado' => 'boolean',
        ];
    }

    public function contaBancaria(): BelongsTo
    {
        return $this->belongsTo(ContaBancaria::class);
    }

    public function conciliacaoPendente(): HasOne
    {
        return $this->hasOne(ConciliacaoPendente::class, 'transacao_financeira_id');
    }
}
