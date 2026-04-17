<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Fornecedor extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'fornecedores';

    protected $fillable = [
        'nome',
        'cnpj',
        'saldo_sucata_kg',
        'saldo_sucata_financeiro',
        'filial_id',
    ];

    protected function casts(): array
    {
        return [
            'saldo_sucata_kg' => 'decimal:2',
            'saldo_sucata_financeiro' => 'decimal:2',
        ];
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
