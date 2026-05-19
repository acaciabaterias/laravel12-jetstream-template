<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FiscalRuleMappingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiscalRuleMapping extends Model
{
    /** @use HasFactory<FiscalRuleMappingFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'fiscal_rule_publication_record_id',
        'scenario_key',
        'cfop_code',
        'classification_code',
        'operation_direction',
        'validation_flags',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'validation_flags' => 'array',
            'metadata' => 'array',
        ];
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(FiscalRulePublicationRecord::class, 'fiscal_rule_publication_record_id');
    }
}
