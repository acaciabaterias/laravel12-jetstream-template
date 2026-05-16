<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::updated(function ($cliente) {
            Cache::forget("tenant:{$cliente->subdominio}");
        });

        static::deleted(function ($cliente) {
            Cache::forget("tenant:{$cliente->subdominio}");
        });
    }

    protected $table = 'clientes';

    protected $fillable = [
        'cnpj', 'razao_social', 'nome_fantasia', 'email_contato', 'telefone',
        'subdominio', 'plano', 'status', 'trial_ends_at', 'subscription_ends_at',
        'plano_atual_id', 'billing_blocked', 'provisioning_status', 'metadata',
        'supabase_project_ref', 'supabase_url', 'supabase_db_host',
        'supabase_db_password', 'supabase_anon_key', 'supabase_service_role_key',
        'endereco', 'saldo_sucata_kg',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'date',
            'subscription_ends_at' => 'date',
            'billing_blocked' => 'boolean',
            'saldo_sucata_kg' => 'decimal:2',
            'metadata' => 'array',
            'supabase_db_password' => 'encrypted',
            'supabase_anon_key' => 'encrypted',
            'supabase_service_role_key' => 'encrypted',
        ];
    }

    public function getConnectionName(): ?string
    {
        try {
            return Schema::connection('central')->hasTable($this->getTable())
                ? 'central'
                : parent::getConnectionName();
        } catch (Throwable) {
            return parent::getConnectionName();
        }
    }

    public function hasActiveSubscription(): bool
    {
        return $this->status === 'active'
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    public function withinTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    public function getRateLimitPerMinute(): int
    {
        return match ($this->plano) {
            'pro' => 600,
            'enterprise' => 6000,
            default => 60, // free
        };
    }

    public function certificadosDigitais(): HasMany
    {
        return $this->hasMany(CertificadoDigital::class);
    }

    public function assinaturasPlataforma(): HasMany
    {
        return $this->hasMany(AssinaturaPlataforma::class);
    }

    public function casosRecuperacaoReceita(): HasMany
    {
        return $this->hasMany(CasoRecuperacaoReceita::class);
    }
}
