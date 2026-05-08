<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Billing\RevenueRecoveryCaseStatus;
use App\Support\Billing\RevenueRecoverySeverity;
use Database\Factories\CasoRecuperacaoReceitaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CasoRecuperacaoReceita extends Model
{
    /** @use HasFactory<CasoRecuperacaoReceitaFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'casos_recuperacao_receita';

    protected $fillable = [
        'cliente_id',
        'assinatura_id',
        'fatura_saas_id',
        'politica_recuperacao_receita_id',
        'status',
        'entry_reason',
        'current_stage',
        'severity',
        'opened_at',
        'closed_at',
        'owner_user_id',
        'last_action_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'status' => RevenueRecoveryCaseStatus::class,
            'severity' => RevenueRecoverySeverity::class,
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_action_at' => 'datetime',
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

    public function fatura(): BelongsTo
    {
        return $this->belongsTo(FaturaSaaS::class, 'fatura_saas_id');
    }

    public function politica(): BelongsTo
    {
        return $this->belongsTo(PoliticaRecuperacaoReceita::class, 'politica_recuperacao_receita_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(UsuarioPlataforma::class, 'owner_user_id');
    }

    public function acoes(): HasMany
    {
        return $this->hasMany(AcaoRecuperacaoReceita::class, 'caso_recuperacao_receita_id');
    }

    public function compromissos(): HasMany
    {
        return $this->hasMany(CompromissoPagamento::class, 'caso_recuperacao_receita_id');
    }
}
