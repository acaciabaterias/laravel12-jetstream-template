<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::name('admin.')->group(function () {
    // Rotas Públicas (Login)
    Route::get('/login', function () {
        return 'Admin Login Page (Coming Soon)';
    })->name('login');

    // Rotas Protegidas
    Route::middleware(['web', \App\Http\Middleware\PlatformAdminMiddleware::class])->group(function () {
        Volt::route('/dashboard', 'admin.dashboard')->name('dashboard');
        Volt::route('/clientes', 'admin.clientes.index')->name('clientes.index');
        Volt::route('/clientes/novo', 'admin.clientes.create')->name('clientes.create');
        Volt::route('/planos', 'admin.planos.index')->name('planos.index');
        Volt::route('/assinaturas', 'admin.assinaturas.index')->name('assinaturas.index');
    });
});
