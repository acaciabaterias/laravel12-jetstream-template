<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CnabRemessa extends Model
{
    use Auditable, HasFactory;

    protected $table = 'cnab_remessas';

    protected $fillable = [
        'tipo_arquivo',
        'nome_arquivo',
        'status',
        'arquivo_path',
    ];

    public function retornos(): HasMany
    {
        return $this->hasMany(CnabRetornoUpload::class, 'cnab_remessa_id');
    }
}
