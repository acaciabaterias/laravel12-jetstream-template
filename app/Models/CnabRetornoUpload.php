<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CnabRetornoUpload extends Model
{
    use Auditable, HasFactory;

    protected $table = 'cnab_retorno_uploads';

    protected $fillable = [
        'cnab_remessa_id',
        'nome_arquivo',
        'status_processamento',
        'log_processamento',
    ];

    public function cnabRemessa(): BelongsTo
    {
        return $this->belongsTo(CnabRemessa::class);
    }
}
