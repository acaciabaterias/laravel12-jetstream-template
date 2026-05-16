<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vale extends Model
{
    use Auditable, HasFactory;

    protected $fillable = [
        'cliente_id',
        'vendedor_id',
        'status',
        'data_criacao',
        'data_faturamento',
        'observacoes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data_criacao' => 'datetime',
            'data_faturamento' => 'datetime',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemVale::class);
    }

    public function reservas(): HasMany
    {
        return $this->hasMany(ReservaEstoque::class);
    }

    public function pedidoVenda(): HasMany
    {
        return $this->hasMany(PedidoVenda::class);
    }

    public function ordensServico(): HasMany
    {
        return $this->hasMany(OrdemServico::class);
    }

    public function getValorTotalAttribute(): float
    {
        return (float) $this->itens->sum(function (ItemVale $item): float {
            return (float) $item->preco_unitario_final * (float) $item->quantidade;
        });
    }
}
