<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacaoWhatsApp extends Model
{
    use HasFactory;

    protected $table = 'notificacao_whats_apps';

    protected $fillable = [
        'os_garantia_id',
        'cliente_telefone',
        'status',
        'mensagem',
        'data_envio',
    ];

    protected function casts(): array
    {
        return [
            'data_envio' => 'datetime',
        ];
    }

    public function osGarantia()
    {
        return $this->belongsTo(OrdemServicoGarantia::class);
    }
}
