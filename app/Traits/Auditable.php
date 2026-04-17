<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    protected function logAudit(string $action)
    {
        $oldValues = $action === 'updated' ? array_intersect_key($this->getOriginal(), $this->getDirty()) : null;
        $newValues = $action === 'updated' ? $this->getDirty() : ($action === 'created' ? $this->getAttributes() : null);

        // Remove campos sensíveis
        $exclude = ['password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'];
        if ($oldValues) {
            $oldValues = array_diff_key($oldValues, array_flip($exclude));
        }
        if ($newValues) {
            $newValues = array_diff_key($newValues, array_flip($exclude));
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $this->getTable(),
            'record_id' => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'filial_id' => $this->filial_id ?? (Auth::check() ? Auth::user()->filial_id : session('filial_id')),
        ]);
    }
}
