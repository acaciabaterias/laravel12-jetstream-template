<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cobranca extends Model
{
    use HasUuids;

    protected $table = 'cobrancas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'idempotency_key',
        'erp_fatura_id',
        'banco_id',
        'tipo',
        'valor',
        'vencimento',
        'nosso_numero',
        'linha_digitavel',
        'codigo_barras',
        'pdf_url',
        'qrcode_pix',
        'qr_code_imagem_base64',
        'link_pagamento',
        'txid',
        'status',
        'pago_em',
        'pago_valor',
    ];

    protected function casts(): array
    {
        return [
            'vencimento' => 'date',
            'pago_em' => 'datetime',
            'valor' => 'decimal:2',
            'pago_valor' => 'decimal:2',
        ];
    }

    public function banco(): BelongsTo
    {
        return $this->belongsTo(BancoPerfil::class, 'banco_id');
    }
}
