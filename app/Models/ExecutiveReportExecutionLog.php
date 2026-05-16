<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExecutiveReportExecutionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExecutiveReportExecutionLog extends Model
{
    /** @use HasFactory<ExecutiveReportExecutionLogFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'executive_report_export_id',
        'event_type',
        'operator_name',
        'operator_id',
        'event_payload',
        'logged_at',
    ];

    protected function casts(): array
    {
        return [
            'event_payload' => 'array',
            'logged_at' => 'datetime',
        ];
    }

    public function reportExport(): BelongsTo
    {
        return $this->belongsTo(ExecutiveReportExport::class, 'executive_report_export_id');
    }
}
