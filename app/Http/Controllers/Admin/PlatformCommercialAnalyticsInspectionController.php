<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformCommercialAnalyticsInspectionRequest;
use App\Services\Billing\PlatformCommercialAnalyticsInspectionService;
use Illuminate\Http\JsonResponse;

class PlatformCommercialAnalyticsInspectionController extends Controller
{
    public function __invoke(
        PlatformCommercialAnalyticsInspectionRequest $request,
        PlatformCommercialAnalyticsInspectionService $platformCommercialAnalyticsInspectionService,
    ): JsonResponse {
        return response()->json(
            $platformCommercialAnalyticsInspectionService->inspect($request->validated())
        );
    }
}
