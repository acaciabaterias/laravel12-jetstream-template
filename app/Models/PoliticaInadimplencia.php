<?php

namespace App\Models;

use Database\Factories\PoliticaInadimplenciaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoliticaInadimplencia extends Model
{
    /** @use HasFactory<PoliticaInadimplenciaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'politicas_inadimplencia';

    protected $fillable = [
        'nome',
        'grace_period_days',
        'block_after_days',
        'reactivation_mode',
        'notification_profile',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'grace_period_days' => 'integer',
            'block_after_days' => 'integer',
            'notification_profile' => 'array',
        ];
    }

    public function assinaturas(): HasMany
    {
        return $this->hasMany(AssinaturaPlataforma::class, 'politica_inadimplencia_id');
    }
}
