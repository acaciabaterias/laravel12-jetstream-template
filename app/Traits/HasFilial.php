<?php

namespace App\Traits;

use App\Models\Filial;
use App\Models\Scopes\MultiTenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Session;

trait HasFilial
{
    /**
     * Boot the trait to apply the Global Scope.
     */
    protected static function bootHasFilial(): void
    {
        static::addGlobalScope(new MultiTenantScope);

        static::creating(function ($model) {
            if (! $model->filial_id && Session::has('filial_id')) {
                $model->filial_id = Session::get('filial_id');
            }
        });
    }

    /**
     * Relationship to the Filial.
     */
    public function filial(): BelongsTo
    {
        return $this->belongsTo(Filial::class);
    }
}
