<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Canal de Logística por Filial - Canal de Presença
 * Autoriza apenas usuários que pertencem à mesma filial.
 */
Broadcast::channel('filial.{filialId}.logistica', function ($user, $filialId) {
    if ((int) $user->filial_id === (int) $filialId) {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role ?? 'user',
        ];
    }
    
    return false;
});
