<?php

namespace App\Models;

use Database\Factories\AssinaturaPlataformaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssinaturaPlataforma extends Model
{
    /** @use HasFactory<AssinaturaPlataformaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'assinaturas';

    protected $fillable = [
        'cliente_id',
        'plano_id',
        'politica_inadimplencia_id',
        'status',
        'data_inicio',
        'data_proximo_ciclo',
        'data_termino',
        'grace_ends_at',
        'blocked_at',
        'blocked_reason',
        'reactivated_at',
        'cancel_reason',
        'stripe_subscription_id',
        'stripe_customer_id',
        'observacoes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_proximo_ciclo' => 'date',
            'data_termino' => 'date',
            'grace_ends_at' => 'date',
            'blocked_at' => 'datetime',
            'reactivated_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(PlanoComercial::class, 'plano_id');
    }

    public function politicaInadimplencia(): BelongsTo
    {
        return $this->belongsTo(PoliticaInadimplencia::class, 'politica_inadimplencia_id');
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(FaturaSaaS::class, 'assinatura_id');
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(EventoComercialAssinante::class, 'assinatura_id');
    }
}
