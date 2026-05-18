<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RecoveryAutomationJourneyStatus;
use Database\Factories\RecoveryAutomationJourneyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecoveryAutomationJourney extends Model
{
    /** @use HasFactory<RecoveryAutomationJourneyFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'caso_recuperacao_receita_id',
        'recovery_automation_policy_version_id',
        'recovery_automation_experiment_id',
        'variant_key',
        'journey_status',
        'current_stage',
        'current_channel',
        'last_dispatched_at',
        'next_evaluation_at',
        'suppressed_until',
        'rollback_marked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'journey_status' => RecoveryAutomationJourneyStatus::class,
            'last_dispatched_at' => 'datetime',
            'next_evaluation_at' => 'datetime',
            'suppressed_until' => 'datetime',
            'rollback_marked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function recoveryCase(): BelongsTo
    {
        return $this->belongsTo(CasoRecuperacaoReceita::class, 'caso_recuperacao_receita_id');
    }

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationPolicyVersion::class, 'recovery_automation_policy_version_id');
    }

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationExperiment::class, 'recovery_automation_experiment_id');
    }

    public function dispatches(): HasMany
    {
        return $this->hasMany(RecoveryAutomationDispatch::class);
    }
}
