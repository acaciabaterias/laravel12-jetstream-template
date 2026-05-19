<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\FiscalCfopCatalogEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FiscalCfopCatalogEntry extends Model
{
    /** @use HasFactory<FiscalCfopCatalogEntryFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'cfop_code',
        'description',
        'operation_direction',
        'is_enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
