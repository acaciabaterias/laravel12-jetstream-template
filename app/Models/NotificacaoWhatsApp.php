<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacaoWhatsApp extends Model
{
    use Auditable, HasFactory;

    protected $table = 'notificacoes_whatsapp';

    protected $fillable = [
        'os_garantia_id',
        'cliente_telefone',
        'status',
        'mensagem',
        'data_envio',
        'identificador_externo',
        'tracking_error',
    ];

    protected function casts(): array
    {
        return [
            'data_envio' => 'datetime',
        ];
    }

    public function ordemServicoGarantia(): BelongsTo
    {
        return $this->belongsTo(OrdemServicoGarantia::class, 'os_garantia_id');
    }
}
