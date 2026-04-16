<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filial extends Model
{
    /** @use HasFactory<\Database\Factories\FilialFactory> */
    use HasFactory;

    protected $table = 'filiais';

    protected $fillable = [
        'nome',
        'cnpj',
        'active',
        'subdominio',
        'dominio_personalizado',
        'email_contato',
        'telefone',
        'plano',
        'status_assinatura',
        'trial_ends_at',
        'subscription_ends_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'max_usuarios',
        'max_estoque_itens',
        'has_support_priority',
        'has_white_label',
        'has_api_integration',
        'configuracoes',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'trial_ends_at' => 'date',
            'subscription_ends_at' => 'date',
            'max_usuarios' => 'integer',
            'max_estoque_itens' => 'integer',
            'has_support_priority' => 'boolean',
            'has_white_label' => 'boolean',
            'has_api_integration' => 'boolean',
            'configuracoes' => 'json',
        ];
    }

    public function whiteLabelConfig()
    {
        return $this->hasOne(WhiteLabelConfig::class);
    }

    public function assinaturas()
    {
        return $this->hasMany(Assinatura::class);
    }

    public function assinaturaAtiva()
    {
        return $this->hasOne(Assinatura::class)->where('status', 'active')->latest();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->status_assinatura === 'active' 
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    public function withinTrial(): bool
    {
        return $this->status_assinatura === 'trial' 
            && $this->trial_ends_at 
            && $this->trial_ends_at->isFuture();
    }

    public function canAccessFeature(string $feature): bool
    {
        return match($feature) {
            'white_label' => $this->has_white_label,
            'api_integration' => $this->has_api_integration,
            default => true,
        };
    }
}
