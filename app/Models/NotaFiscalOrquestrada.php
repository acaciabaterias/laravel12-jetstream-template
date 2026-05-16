<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotaFiscalOrquestrada extends Model
{
    use Auditable, HasFactory;

    protected $table = 'notas_fiscais_orquestradas';

    protected $fillable = [
        'vale_id',
        'chave_acesso',
        'xml_path',
        'status',
        'ms_requisicao_id',
        'idempotency_key',
        'certificado_digital_id',
        'certificado_referencia',
    ];

    public function vale(): BelongsTo
    {
        return $this->belongsTo(Vale::class);
    }
}
