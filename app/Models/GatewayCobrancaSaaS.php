<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\GatewayCobrancaSaaSFactory;
use App\Support\Billing\GatewayOperationalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GatewayCobrancaSaaS extends Model
{
    /** @use HasFactory<GatewayCobrancaSaaSFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'gateways_cobranca_saas';

    protected $fillable = [
        'nome',
        'slug',
        'driver',
        'status',
        'supported_channels',
        'credential_profile',
        'timeout_seconds',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => GatewayOperationalStatus::class,
            'supported_channels' => 'array',
            'credential_profile' => 'array',
            'timeout_seconds' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function cobrancas(): HasMany
    {
        return $this->hasMany(CobrancaSaaSExterna::class, 'gateway_cobranca_saas_id');
    }

    public function retornos(): HasMany
    {
        return $this->hasMany(RetornoPagamentoSaaS::class, 'gateway_cobranca_saas_id');
    }
}
