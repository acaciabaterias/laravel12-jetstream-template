<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'clientes';

    protected $fillable = [
        'cnpj', 'razao_social', 'nome_fantasia', 'email_contato', 'telefone',
        'subdominio', 'plano', 'status', 'trial_ends_at', 'subscription_ends_at',
        'supabase_project_ref', 'supabase_url', 'supabase_db_host',
        'supabase_db_password', 'supabase_anon_key', 'supabase_service_role_key',
        'endereco', 'saldo_sucata_kg',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'date',
            'subscription_ends_at' => 'date',
            'saldo_sucata_kg' => 'decimal:2',
            'supabase_db_password' => 'encrypted',
            'supabase_anon_key' => 'encrypted',
            'supabase_service_role_key' => 'encrypted',
        ];
    }

    public function hasActiveSubscription(): bool
    {
        return $this->status === 'active'
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    public function withinTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }
}
