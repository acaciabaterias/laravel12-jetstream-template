<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdemServicoGarantia extends Model
{
    use Auditable, HasFactory;

    protected $table = 'ordens_servico_garantia';

    protected $fillable = [
        'cliente_id',
        'bateria_id',
        'vale_original_id',
        'data_abertura',
        'status',
        'laudo',
        'resultado',
        'cobranca_valor',
    ];

    protected function casts(): array
    {
        return [
            'data_abertura' => 'datetime',
            'cobranca_valor' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function bateria(): BelongsTo
    {
        return $this->belongsTo(Bateria::class);
    }

    public function valeOriginal(): BelongsTo
    {
        return $this->belongsTo(Vale::class, 'vale_original_id');
    }

    public function bateriasEmprestimo(): HasMany
    {
        return $this->hasMany(BateriaEmprestimo::class, 'os_garantia_id');
    }

    public function notificacoes(): HasMany
    {
        return $this->hasMany(NotificacaoWhatsApp::class, 'os_garantia_id');
    }
}
