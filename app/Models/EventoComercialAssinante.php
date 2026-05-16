<?php

namespace App\Models;

use Database\Factories\EventoComercialAssinanteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoComercialAssinante extends Model
{
    /** @use HasFactory<EventoComercialAssinanteFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'eventos_comerciais_assinante';

    protected $fillable = [
        'cliente_id',
        'assinatura_id',
        'actor_user_id',
        'event_type',
        'before_state',
        'after_state',
        'effective_at',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'before_state' => 'array',
            'after_state' => 'array',
            'effective_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(AssinaturaPlataforma::class, 'assinatura_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'actor_user_id');
    }
}
