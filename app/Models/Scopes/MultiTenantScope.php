<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class MultiTenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check() && ! Auth::user()->isSuperAdmin()) {
            $builder->where($model->getTable().'.filial_id', Auth::user()->filial_id);
        } elseif (session()->has('filial_id')) {
            // Suporte para super_admin que selecionou um contexto de filial na sessão
            $builder->where($model->getTable().'.filial_id', session('filial_id'));
        }
    }
}
