<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RecoveryAutomationDispatchStatus;
use Database\Factories\RecoveryAutomationDispatchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryAutomationDispatch extends Model
{
    /** @use HasFactory<RecoveryAutomationDispatchFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'recovery_automation_journey_id',
        'acao_recuperacao_receita_id',
        'dispatch_key',
        'stage_key',
        'channel',
        'template_key',
        'attempt_number',
        'dispatch_status',
        'fallback_reason',
        'scheduled_for',
        'dispatched_at',
        'result_payload',
        'operator_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'attempt_number' => 'integer',
            'dispatch_status' => RecoveryAutomationDispatchStatus::class,
            'scheduled_for' => 'datetime',
            'dispatched_at' => 'datetime',
            'result_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationJourney::class, 'recovery_automation_journey_id');
    }

    public function recoveryAction(): BelongsTo
    {
        return $this->belongsTo(AcaoRecuperacaoReceita::class, 'acao_recuperacao_receita_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_id');
    }
}
