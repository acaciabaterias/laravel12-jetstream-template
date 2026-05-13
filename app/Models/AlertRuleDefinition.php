<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\MonitoringProvisioningStatus;
use App\Support\Operations\MonitoringSeverity;
use Database\Factories\AlertRuleDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertRuleDefinition extends Model
{
    /** @use HasFactory<AlertRuleDefinitionFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'alert_rule_definitions';

    protected $fillable = [
        'flow_name',
        'rule_name',
        'severity',
        'version',
        'condition_summary',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'severity' => MonitoringSeverity::class,
            'status' => MonitoringProvisioningStatus::class,
            'metadata' => 'array',
        ];
    }
}
