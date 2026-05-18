<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RecoveryAutomationExperimentStatus;
use Database\Factories\RecoveryAutomationExperimentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecoveryAutomationExperiment extends Model
{
    /** @use HasFactory<RecoveryAutomationExperimentFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'recovery_automation_policy_version_id',
        'name',
        'status',
        'allocation_rules',
        'control_ratio',
        'variant_definitions',
        'started_at',
        'ended_at',
        'created_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecoveryAutomationExperimentStatus::class,
            'allocation_rules' => 'array',
            'control_ratio' => 'decimal:2',
            'variant_definitions' => 'array',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationPolicyVersion::class, 'recovery_automation_policy_version_id');
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(RecoveryAutomationJourney::class, 'recovery_automation_experiment_id');
    }
}
