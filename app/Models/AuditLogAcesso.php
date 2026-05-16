<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogAcesso extends Model
{
    use HasFactory;

    protected $table = 'audit_logs_acesso';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ip',
        'user_agent',
        'sucesso',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'sucesso' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
