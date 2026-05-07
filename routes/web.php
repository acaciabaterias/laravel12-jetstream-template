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

    Route::get('/integration/backbone', function () {
        abort_unless(auth()->user()->can('view-integration-operations'), 403);

        return view('integration.backbone');
    })->name('integration.backbone');
});
