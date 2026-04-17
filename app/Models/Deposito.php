<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([MultiTenantScope::class])]
class Deposito extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'nome',
        'status',
        'filial_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }
}
