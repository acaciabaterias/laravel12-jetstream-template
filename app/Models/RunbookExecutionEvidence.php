<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\RunbookResultStatus;
use Database\Factories\RunbookExecutionEvidenceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RunbookExecutionEvidence extends Model
{
    /** @use HasFactory<RunbookExecutionEvidenceFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'runbook_execution_evidences';

    protected $fillable = [
        'operational_incident_record_id',
        'execution_type',
        'operator_user_id',
        'started_at',
        'finished_at',
        'result_status',
        'evidence_payload',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'result_status' => RunbookResultStatus::class,
            'evidence_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function incident(): BelongsTo
    {
        return $this->belongsTo(OperationalIncidentRecord::class, 'operational_incident_record_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_user_id');
    }
}
