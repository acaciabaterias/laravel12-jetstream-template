<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'filial.isolation'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/sync/mobile', [\App\Http\Controllers\Api\MobileSyncController::class, 'sync']);
});

// Rotas internas para comunicação com Microserviços e Monitoramento
Route::middleware(['internal.auth'])->prefix('internal/webhooks')->group(function () {
    Route::post('/fiscal/status', function (Request $request) {
        return response()->json(['status' => 'received', 'message' => 'Fiscal webhook processed.']);
    });
});

Route::middleware(['internal.auth'])->get('/metrics', function () {
    $registry = \Prometheus\CollectorRegistry::getDefault();
    $renderer = new \Prometheus\RenderTextFormat();
    $result = $renderer->render($registry->getMetricFamilySamples());
    return response($result, 200)->header('Content-Type', \Prometheus\RenderTextFormat::MIME_TYPE);
});
