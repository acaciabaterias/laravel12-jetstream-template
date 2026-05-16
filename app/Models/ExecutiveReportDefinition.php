<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ExecutiveReportDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExecutiveReportDefinition extends Model
{
    /** @use HasFactory<ExecutiveReportDefinitionFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'slug',
        'name',
        'description',
        'default_filters',
        'visible_sections',
        'supported_formats',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'default_filters' => 'array',
            'visible_sections' => 'array',
            'supported_formats' => 'array',
        ];
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ExecutiveReportExport::class);
    }
}
