<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RevenueRecoveryPolicyStatus;
use Database\Factories\PoliticaRecuperacaoReceitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoliticaRecuperacaoReceita extends Model
{
    /** @use HasFactory<PoliticaRecuperacaoReceitaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'politicas_recuperacao_receita';

    protected $fillable = [
        'nome',
        'slug',
        'status',
        'entry_conditions',
        'stage_definitions',
        'escalation_rules',
        'reengagement_rules',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => RevenueRecoveryPolicyStatus::class,
            'entry_conditions' => 'array',
            'stage_definitions' => 'array',
            'escalation_rules' => 'array',
            'reengagement_rules' => 'array',
            'metadata' => 'array',
        ];
    }

    public function casos(): HasMany
    {
        return $this->hasMany(CasoRecuperacaoReceita::class, 'politica_recuperacao_receita_id');
    }
}
