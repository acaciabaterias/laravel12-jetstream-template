<?php

namespace App\Models;

use App\Models\Scopes\MultiTenantScope;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ScopedBy([MultiTenantScope::class])]
#[ObservedBy([\App\Observers\OrdemServicoGarantiaObserver::class])]
class OrdemServicoGarantia extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'cliente_id',
        'bateria_id',
        'vale_original_id',
        'filial_id',
        'status',
        'laudo',
        'resultado',
        'numero_serie',
        'data_abertura',
        'data_conclusao',
    ];

    protected function casts(): array
    {
        return [
            'data_abertura' => 'datetime',
            'data_conclusao' => 'datetime',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function bateria()
    {
        return $this->belongsTo(Bateria::class);
    }

    public function valeOriginal()
    {
        return $this->belongsTo(Vale::class, 'vale_original_id');
    }

    public function filial()
    {
        return $this->belongsTo(Filial::class);
    }

    public function emprestimos()
    {
        return $this->hasMany(BateriaEmprestimo::class);
    }

    public function notificacoes()
    {
        return $this->hasMany(NotificacaoWhatsApp::class);
    }
}
