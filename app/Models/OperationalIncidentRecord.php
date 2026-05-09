<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\OperationalIncidentStatus;
use App\Support\Operations\OperationalSeverity;
use Database\Factories\OperationalIncidentRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OperationalIncidentRecord extends Model
{
    /** @use HasFactory<OperationalIncidentRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'operational_incident_records';

    protected $fillable = [
        'incident_key',
        'flow_name',
        'severity',
        'status',
        'opened_at',
        'acknowledged_at',
        'resolved_at',
        'summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'severity' => OperationalSeverity::class,
            'status' => OperationalIncidentStatus::class,
            'opened_at' => 'datetime',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(RunbookExecutionEvidence::class, 'operational_incident_record_id');
    }
}
