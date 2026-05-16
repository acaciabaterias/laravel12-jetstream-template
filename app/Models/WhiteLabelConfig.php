<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Throwable;

class WhiteLabelConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'cliente_id',
        'logo_url', 'favicon_url', 'cor_primaria',
        'cor_secundaria', 'cor_fundo', 'titulo_login', 'custom_css',
        'custom_js', 'template_nome', 'mostrar_marca_plataforma',
    ];

    protected function casts(): array
    {
        return [
            'mostrar_marca_plataforma' => 'boolean',
        ];
    }

    public function getConnectionName(): ?string
    {
        try {
            return Schema::connection('central')->hasTable($this->getTable())
                ? 'central'
                : parent::getConnectionName();
        } catch (Throwable) {
            return parent::getConnectionName();
        }
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
