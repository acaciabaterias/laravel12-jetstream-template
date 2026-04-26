<?php

use App\Http\Controllers\Api\V1\FiscalController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/nfe/emitir', [FiscalController::class, 'emitirNfe']);
    Route::post('/nfce/emitir', [FiscalController::class, 'emitirNfce']);
    Route::post('/nfe/cancelar', [FiscalController::class, 'cancelar']);
    Route::post('/nfe/cce', [FiscalController::class, 'cce']);
    Route::post('/nfe/inutilizar', [FiscalController::class, 'inutilizar']);
    Route::get('/nfe/{chaveAcesso}', [FiscalController::class, 'consultar']);
    Route::get('/contingencia/fila', [FiscalController::class, 'filaContingencia']);
    Route::get('/certificado/status', [FiscalController::class, 'certificadoStatus']);
    Route::get('/health', [FiscalController::class, 'health']);
});
