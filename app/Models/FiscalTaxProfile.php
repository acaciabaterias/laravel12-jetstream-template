<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FiscalTaxProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalTaxProfile extends Model
{
    /** @use HasFactory<FiscalTaxProfileFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'fiscal_rule_mapping_id',
        'fiscal_rule_publication_record_id',
        'scenario_key',
        'cfop_code',
        'ncm_code',
        'tax_regime',
        'cst_code',
        'csosn_code',
        'partner_type',
        'operation_purpose',
        'origin_state',
        'destination_state',
        'interstate_tax_rate',
        'tax_payload',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'interstate_tax_rate' => 'decimal:2',
            'tax_payload' => 'array',
            'metadata' => 'array',
        ];
    }

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(FiscalRuleMapping::class, 'fiscal_rule_mapping_id');
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(FiscalRulePublicationRecord::class, 'fiscal_rule_publication_record_id');
    }
}
