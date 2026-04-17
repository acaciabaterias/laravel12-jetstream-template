<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fatura extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'assinatura_id', 'stripe_invoice_id', 'numero', 'valor',
        'data_vencimento', 'data_pagamento', 'status', 'pdf_url',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
            'data_vencimento' => 'date',
            'data_pagamento' => 'date',
        ];
    }

    public function assinatura()
    {
        return $this->belongsTo(Assinatura::class);
    }
}
