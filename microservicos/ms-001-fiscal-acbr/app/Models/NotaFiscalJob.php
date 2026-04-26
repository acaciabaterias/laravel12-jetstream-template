<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class NotaFiscalJob extends Model
{
    use HasUuids;

    protected $table = 'nota_fiscal_jobs';

    protected $fillable = [
        'vale_id',
        'tipo',
        'payload',
        'xml_assinado',
        'chave_acesso',
        'protocolo',
        'status',
        'tentativas',
        'proxima_tentativa',
        'correlation_id',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'proxima_tentativa' => 'datetime',
        ];
    }
}
