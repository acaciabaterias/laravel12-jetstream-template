<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RecoveryAutomationViolationSeverity;
use Database\Factories\RecoveryAutomationViolationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecoveryAutomationViolation extends Model
{
    /** @use HasFactory<RecoveryAutomationViolationFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'recovery_automation_policy_version_id',
        'recovery_automation_journey_id',
        'recovery_automation_dispatch_id',
        'violation_type',
        'severity',
        'detected_at',
        'resolved_at',
        'resolution_status',
        'summary',
        'evidence_payload',
        'resolved_by',
    ];

    protected function casts(): array
    {
        return [
            'severity' => RecoveryAutomationViolationSeverity::class,
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'evidence_payload' => 'array',
        ];
    }

    public function policyVersion(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationPolicyVersion::class, 'recovery_automation_policy_version_id');
    }

    public function journey(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationJourney::class, 'recovery_automation_journey_id');
    }

    public function dispatch(): BelongsTo
    {
        return $this->belongsTo(RecoveryAutomationDispatch::class, 'recovery_automation_dispatch_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'resolved_by');
    }
}
