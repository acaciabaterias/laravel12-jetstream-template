<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XmlImportacao extends Model
{
    use Auditable, HasFactory;

    protected $table = 'xml_importacoes';

    protected $fillable = [
        'chave_nfe',
        'fornecedor_id',
        'status',
        'log_erros',
        'payload_xml',
    ];

    protected function casts(): array
    {
        return [
            'payload_xml' => 'json',
        ];
    }
}
