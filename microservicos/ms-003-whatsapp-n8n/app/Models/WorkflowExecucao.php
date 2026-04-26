<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkflowExecucao extends Model
{
    use HasUuids;

    protected $table = 'workflow_execucaos';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'workflow_name',
        'evento_trigger',
        'status',
        'payload_entrada',
        'mensagem_enviada',
        'canal',
        'destinatario',
    ];

    protected function casts(): array
    {
        return [
            'payload_entrada' => 'array',
        ];
    }
}
