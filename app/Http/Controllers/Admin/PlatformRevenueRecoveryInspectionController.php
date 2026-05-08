<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformRevenueRecoveryInspectionRequest;
use App\Services\Billing\PlatformRevenueRecoveryInspectionService;
use Illuminate\Http\JsonResponse;

class PlatformRevenueRecoveryInspectionController extends Controller
{
    public function __invoke(
        PlatformRevenueRecoveryInspectionRequest $request,
        PlatformRevenueRecoveryInspectionService $platformRevenueRecoveryInspectionService,
    ): JsonResponse {
        return response()->json(
            $platformRevenueRecoveryInspectionService->inspect($request->validated())
        );
    }
}
