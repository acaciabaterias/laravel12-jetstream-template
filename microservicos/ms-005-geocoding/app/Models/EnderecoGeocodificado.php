<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnderecoGeocodificado extends Model
{
    protected $table = 'endereco_geocodificados';

    protected $fillable = [
        'endereco_hash',
        'logradouro',
        'numero',
        'bairro',
        'cidade',
        'uf',
        'cep',
        'latitude',
        'longitude',
        'provider_usado',
        'confianca',
        'ajustado_manualmente',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'ajustado_manualmente' => 'boolean',
        ];
    }
}
