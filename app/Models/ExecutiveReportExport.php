<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\ExecutiveReportExportStatus;
use App\Support\Billing\ExecutiveReportFormat;
use Database\Factories\ExecutiveReportExportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutiveReportExport extends Model
{
    /** @use HasFactory<ExecutiveReportExportFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'executive_analytics_snapshot_id',
        'executive_report_definition_id',
        'reexecuted_from_export_id',
        'format',
        'file_reference',
        'export_status',
        'requested_by',
        'requested_at',
        'completed_at',
        'scope_summary',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'format' => ExecutiveReportFormat::class,
            'export_status' => ExecutiveReportExportStatus::class,
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(ExecutiveAnalyticsSnapshot::class, 'executive_analytics_snapshot_id');
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(ExecutiveReportDefinition::class, 'executive_report_definition_id');
    }

    public function reexecutedFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reexecuted_from_export_id');
    }

    public function executionLogs(): HasMany
    {
        return $this->hasMany(ExecutiveReportExecutionLog::class);
    }
}
