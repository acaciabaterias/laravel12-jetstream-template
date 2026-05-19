<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\PlatformCurrencyCatalogEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformCurrencyCatalogEntry extends Model
{
    /** @use HasFactory<PlatformCurrencyCatalogEntryFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'currency_code',
        'display_name',
        'symbol',
        'decimal_scale',
        'is_enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'decimal_scale' => 'integer',
            'is_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
