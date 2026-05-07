<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CertificadoDigital extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';

    protected $table = 'certificados_digitais';

    protected $fillable = [
        'cliente_id',
        'nome_referencia',
        'finalidade',
        'modelo',
        'formato',
        'conteudo_certificado',
        'senha_certificado',
        'serial_numero',
        'emissor',
        'titular_documento',
        'validade_inicio',
        'validade_fim',
        'status',
        'prioridade',
        'revoked_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'conteudo_certificado' => 'encrypted',
            'senha_certificado' => 'encrypted',
            'validade_inicio' => 'date',
            'validade_fim' => 'date',
            'revoked_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
