<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RecoveryAutomationPolicyStatus;
use Database\Factories\RecoveryAutomationPolicyVersionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecoveryAutomationPolicyVersion extends Model
{
    /** @use HasFactory<RecoveryAutomationPolicyVersionFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'status',
        'scope_filters',
        'guardrail_rules',
        'fallback_matrix',
        'activation_started_at',
        'activation_completed_at',
        'superseded_by_policy_version_id',
        'created_by',
        'approved_by',
        'rolled_back_by',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => RecoveryAutomationPolicyStatus::class,
            'scope_filters' => 'array',
            'guardrail_rules' => 'array',
            'fallback_matrix' => 'array',
            'activation_started_at' => 'datetime',
            'activation_completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function journeys(): HasMany
    {
        return $this->hasMany(RecoveryAutomationJourney::class);
    }

    public function experiments(): HasMany
    {
        return $this->hasMany(RecoveryAutomationExperiment::class);
    }

    public function violations(): HasMany
    {
        return $this->hasMany(RecoveryAutomationViolation::class);
    }

    public function supersededBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'superseded_by_policy_version_id');
    }
}
