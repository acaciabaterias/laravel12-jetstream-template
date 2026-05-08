<?php

namespace App\Models;

use App\Services\Integration\IntegrationStorageManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContratoEvento extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'contratos_evento';

    protected $fillable = [
        'event_type',
        'event_version',
        'producer',
        'status',
        'consumers',
        'schema_definition',
        'compatibility_notes',
        'deprecated_at',
    ];

    protected function casts(): array
    {
        return [
            'consumers' => 'array',
            'schema_definition' => 'array',
            'deprecated_at' => 'datetime',
        ];
    }

    public function getConnectionName(): ?string
    {
        return app(IntegrationStorageManager::class)->currentConnection();
    }
}
