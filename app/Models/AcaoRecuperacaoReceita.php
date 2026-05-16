<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RevenueRecoveryActionStatus;
use App\Support\Billing\RevenueRecoveryActionType;
use Database\Factories\AcaoRecuperacaoReceitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcaoRecuperacaoReceita extends Model
{
    /** @use HasFactory<AcaoRecuperacaoReceitaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'acoes_recuperacao_receita';

    protected $fillable = [
        'caso_recuperacao_receita_id',
        'action_type',
        'channel',
        'stage_name',
        'status',
        'idempotency_key',
        'scheduled_for',
        'executed_at',
        'result_code',
        'operator_user_id',
        'payload_snapshot',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'action_type' => RevenueRecoveryActionType::class,
            'status' => RevenueRecoveryActionStatus::class,
            'scheduled_for' => 'datetime',
            'executed_at' => 'datetime',
            'payload_snapshot' => 'array',
            'metadata' => 'array',
        ];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoRecuperacaoReceita::class, 'caso_recuperacao_receita_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_user_id');
    }
}
