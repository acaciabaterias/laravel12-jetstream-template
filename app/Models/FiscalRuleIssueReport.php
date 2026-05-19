<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Fiscal\FiscalRuleIssueResolutionStatus;
use App\Support\Fiscal\FiscalRuleIssueSeverity;
use Database\Factories\FiscalRuleIssueReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalRuleIssueReport extends Model
{
    /** @use HasFactory<FiscalRuleIssueReportFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'fiscal_rule_publication_record_id',
        'scenario_key',
        'issue_type',
        'severity',
        'resolution_status',
        'detected_at',
        'resolved_at',
        'resolved_by',
        'issue_payload',
    ];

    protected function casts(): array
    {
        return [
            'severity' => FiscalRuleIssueSeverity::class,
            'resolution_status' => FiscalRuleIssueResolutionStatus::class,
            'detected_at' => 'datetime',
            'resolved_at' => 'datetime',
            'issue_payload' => 'array',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(FiscalRulePublicationRecord::class, 'fiscal_rule_publication_record_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'resolved_by');
    }
}
