<?php

use App\Http\Controllers\Api\V1\GeocodingController;
use App\Http\Controllers\Api\V1\LocalizationController;
use App\Http\Controllers\Api\V1\RoutingController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/geocodificar', [GeocodingController::class, 'geocode']);
    Route::put('/geocodificar/corrigir', [GeocodingController::class, 'correct']);
    Route::post('/rotas/otimizar', [RoutingController::class, 'otimizar']);
    Route::get('/rotas/{id}', [RoutingController::class, 'show']);
    Route::post('/localizacao', [LocalizationController::class, 'store']);
    Route::get('/localizacao/{entregadorId}', [LocalizationController::class, 'show']);
    Route::post('/eta/recalcular', [RoutingController::class, 'recalculateEta']);
    Route::delete('/cache/geocodificacao/{hash}', [GeocodingController::class, 'invalidate']);
    Route::get('/health', [GeocodingController::class, 'health']);
});
