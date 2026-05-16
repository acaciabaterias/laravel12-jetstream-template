<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Operations\ThemePublicationStatus;
use Database\Factories\ThemePublicationRecordFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemePublicationRecord extends Model
{
    /** @use HasFactory<ThemePublicationRecordFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'theme_publication_records';

    protected $fillable = [
        'tenant_theme_version_id',
        'environment',
        'operator_id',
        'validation_passed',
        'validation_messages',
        'published_snapshot',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'validation_passed' => 'boolean',
            'validation_messages' => 'array',
            'published_snapshot' => 'array',
            'status' => ThemePublicationStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function themeVersion(): BelongsTo
    {
        return $this->belongsTo(TenantThemeVersion::class, 'tenant_theme_version_id');
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'operator_id');
    }
}
