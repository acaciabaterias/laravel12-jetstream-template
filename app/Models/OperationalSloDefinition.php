<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OperationalSloDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationalSloDefinition extends Model
{
    /** @use HasFactory<OperationalSloDefinitionFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'operational_slo_definitions';

    protected $fillable = [
        'flow_name',
        'metric_key',
        'target_value',
        'warning_threshold',
        'critical_threshold',
        'severity_mapping',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:4',
            'warning_threshold' => 'decimal:4',
            'critical_threshold' => 'decimal:4',
            'severity_mapping' => 'array',
            'metadata' => 'array',
        ];
    }
}
