<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EndpointIntegracao extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $table = 'endpoints_integracao';

    protected $fillable = [
        'service_name',
        'route_name',
        'method',
        'target_url',
        'auth_mode',
        'timeout_ms',
        'rate_limit_per_minute',
        'circuit_breaker_enabled',
        'status',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rate_limit_per_minute' => 'integer',
            'timeout_ms' => 'integer',
            'circuit_breaker_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
