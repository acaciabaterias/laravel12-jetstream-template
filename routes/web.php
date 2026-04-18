<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'filial.isolation',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulo 006 - App do Entregador
    // Módulo 008 - Suporte e Rastreabilidade
    Route::get('/suporte', \App\Livewire\SuporteCentral::class)->name('suporte.central');
});
