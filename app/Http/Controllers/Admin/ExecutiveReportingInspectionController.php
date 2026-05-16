<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExecutiveReportingInspectionRequest;
use App\Services\Billing\ExecutiveReportingInspectionService;
use Illuminate\Http\JsonResponse;

class ExecutiveReportingInspectionController extends Controller
{
    public function __invoke(
        ExecutiveReportingInspectionRequest $request,
        ExecutiveReportingInspectionService $executiveReportingInspectionService,
    ): JsonResponse {
        return response()->json(
            $executiveReportingInspectionService->inspect($request->validated())
        );
    }
}
