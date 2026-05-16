<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductionObservabilityInspectionRequest;
use App\Services\Operations\ProductionObservabilityInspectionService;
use Illuminate\Http\JsonResponse;

class ProductionObservabilityInspectionController extends Controller
{
    public function __invoke(
        ProductionObservabilityInspectionRequest $request,
        ProductionObservabilityInspectionService $productionObservabilityInspectionService,
    ): JsonResponse {
        return response()->json(
            $productionObservabilityInspectionService->inspect($request->validated())
        );
    }
}
