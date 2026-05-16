<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BackboneMonitoringInspectionRequest;
use App\Services\Operations\BackboneMonitoringInspectionService;
use Illuminate\Http\JsonResponse;

class BackboneMonitoringInspectionController extends Controller
{
    public function __invoke(
        BackboneMonitoringInspectionRequest $request,
        BackboneMonitoringInspectionService $backboneMonitoringInspectionService,
    ): JsonResponse {
        return response()->json(
            $backboneMonitoringInspectionService->inspect($request->validated())
        );
    }
}
