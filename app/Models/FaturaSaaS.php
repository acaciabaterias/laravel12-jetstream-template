<?php

namespace App\Models;

use Database\Factories\FaturaSaaSFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FaturaSaaS extends Model
{
    /** @use HasFactory<FaturaSaaSFactory> */
    use HasFactory;

    protected $connection = 'central';

    protected $table = 'faturas';

    protected $fillable = [
        'assinatura_id',
        'cliente_id',
        'referencia',
        'vencimento',
        'valor',
        'valor_pago',
        'status',
        'external_invoice_id',
        'billing_channel',
        'paid_at',
        'payload_gateway',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'valor_pago' => 'decimal:2',
            'vencimento' => 'date',
            'paid_at' => 'datetime',
            'payload_gateway' => 'array',
            'metadata' => 'array',
        ];
    }

    public function assinatura(): BelongsTo
    {
        return $this->belongsTo(AssinaturaPlataforma::class, 'assinatura_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }
}
