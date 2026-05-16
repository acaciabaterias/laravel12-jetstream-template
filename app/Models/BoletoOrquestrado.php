<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoletoOrquestrado extends Model
{
    use Auditable, HasFactory;

    protected $table = 'boletos_orquestrados';

    protected $fillable = [
        'vale_id',
        'nosso_numero',
        'linha_digitavel',
        'pdf_url',
        'status',
        'identificador_externo',
        'idempotency_key',
        'certificado_digital_id',
        'certificado_referencia',
    ];

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }
}
