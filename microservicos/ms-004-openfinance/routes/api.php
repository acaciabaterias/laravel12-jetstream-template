<?php

use App\Http\Controllers\Api\V1\CaptureController;
use App\Http\Controllers\Api\V1\OAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/oauth/authorize/{banco}', [OAuthController::class, 'authorizeProvider']);
    Route::get('/oauth/callback', [OAuthController::class, 'callback']);
    Route::get('/consentimentos', [OAuthController::class, 'index']);
    Route::delete('/consentimentos/{id}', [OAuthController::class, 'destroy']);
    Route::post('/extratos/capturar/{consentimentoId}', [CaptureController::class, 'capturar']);
    Route::get('/transacoes', [CaptureController::class, 'transacoes']);
    Route::get('/captura/logs', [CaptureController::class, 'logs']);
    Route::get('/health', [OAuthController::class, 'health']);
});
