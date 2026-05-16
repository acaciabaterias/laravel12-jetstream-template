<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ThemeAssetRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeAssetRecord extends Model
{
    /** @use HasFactory<ThemeAssetRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'theme_asset_records';

    protected $fillable = [
        'brand_identity_profile_id',
        'asset_type',
        'storage_reference',
        'mime_type',
        'checksum',
        'status',
    ];

    public function brandIdentityProfile(): BelongsTo
    {
        return $this->belongsTo(BrandIdentityProfile::class, 'brand_identity_profile_id');
    }
}
