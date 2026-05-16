<?php

use App\Http\Controllers\Api\V1\BlacklistController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/notificacao/enviar', [NotificationController::class, 'enviar']);
    Route::get('/notificacao/historico/{clienteId}', [NotificationController::class, 'historico']);
    Route::get('/fila', [NotificationController::class, 'fila']);
    Route::get('/blacklist', [BlacklistController::class, 'index']);
    Route::post('/blacklist', [BlacklistController::class, 'store']);
    Route::delete('/blacklist/{numero}', [BlacklistController::class, 'destroy']);
    Route::get('/health', [NotificationController::class, 'health']);
    Route::post('/webhook/evolution', [WebhookController::class, 'handleEvolution']);
    Route::post('/webhook/erp/{evento}', [WebhookController::class, 'handleErp']);
});
