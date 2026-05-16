<?php

use App\Http\Controllers\Api\V1\BankingController;
use App\Http\Controllers\Api\V1\CNABController;
use App\Http\Controllers\Api\V1\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/boleto', [BankingController::class, 'emitirBoleto']);
    Route::post('/pix', [BankingController::class, 'emitirPix']);
    Route::get('/cobranca/{id}', [BankingController::class, 'consultar']);
    Route::delete('/cobranca/{id}', [BankingController::class, 'cancelar']);
    Route::post('/cnab/remessa', [CNABController::class, 'gerarRemessa']);
    Route::post('/cnab/retorno', [CNABController::class, 'processarRetorno']);
    Route::post('/webhook/{banco}', [WebhookController::class, 'handle']);
    Route::get('/bancos', [BankingController::class, 'listarBancos']);
    Route::get('/health', [BankingController::class, 'health']);
});
