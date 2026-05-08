<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assinatura extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'cliente_id', 'plano_id', 'status', 'data_inicio',
        'data_proximo_ciclo', 'data_termino', 'stripe_subscription_id',
        'stripe_customer_id',
    ];

    protected function casts(): array
    {
        return [
            'data_inicio' => 'date',
            'data_proximo_ciclo' => 'date',
            'data_termino' => 'date',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function plano(): BelongsTo
    {
        return $this->belongsTo(PlanoAssinatura::class);
    }

    public function faturas(): HasMany
    {
        return $this->hasMany(Fatura::class);
    }

    public function certificadosDigitais(): HasManyThrough
    {
        return $this->hasManyThrough(
            CertificadoDigital::class,
            Cliente::class,
            'id',
            'cliente_id',
            'cliente_id',
            'id'
        );
    }
}
