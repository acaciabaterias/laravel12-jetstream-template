<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'filial.isolation'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/sync/mobile', [\App\Http\Controllers\Api\MobileSyncController::class, 'sync']);

    // Module 006 - Logistics
    Route::post('/logistics/sync', [\App\Http\Controllers\Api\V1\LogisticsSyncController::class, 'sync']);
});
