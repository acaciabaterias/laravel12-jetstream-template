<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FiscalOperationScenarioFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalOperationScenario extends Model
{
    /** @use HasFactory<FiscalOperationScenarioFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'scenario_key',
        'display_name',
        'operation_direction',
        'is_required',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
