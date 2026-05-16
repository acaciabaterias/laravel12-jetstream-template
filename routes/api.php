<?php

use App\Http\Controllers\Api\IntegrationInspectionController;
use App\Http\Controllers\Api\InternalWebhookController;
use App\Http\Controllers\Api\MobileSyncController;
use App\Services\Integration\IntegrationMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use L5Swagger\Http\Controllers\SwaggerController;
use Prometheus\RenderTextFormat;

Route::middleware(['auth:sanctum', 'filial.isolation'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/sync/mobile', [MobileSyncController::class, 'sync']);
});

Route::middleware(['web', 'auth', 'filial.isolation'])->group(function () {
    Route::get('/integration/inspections', [IntegrationInspectionController::class, 'index']);
    Route::post('/integration/inspections/replay', [IntegrationInspectionController::class, 'replay']);
});

// Rotas internas para comunicação com Microserviços e Monitoramento
Route::middleware(['internal.auth'])->prefix('internal/webhooks')->group(function () {
    Route::post('/fiscal/status', [InternalWebhookController::class, 'fiscalStatus']);
});

Route::get('/api/docs', [SwaggerController::class, 'api'])->middleware('auth');

Route::middleware(['internal.auth'])->get('/metrics', function () {
    $metrics = app(IntegrationMetrics::class);
    $metrics->syncOperationalSnapshot();

    $renderer = new RenderTextFormat;
    $result = $renderer->render($metrics->getMetricFamilySamples());

    return response($result, 200)->header('Content-Type', RenderTextFormat::MIME_TYPE);
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});
