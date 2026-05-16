<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformPaymentsInspectionRequest;
use App\Services\Billing\PlatformPaymentsInspectionService;
use Illuminate\Http\JsonResponse;

class PlatformPaymentsInspectionController extends Controller
{
    public function __invoke(
        PlatformPaymentsInspectionRequest $request,
        PlatformPaymentsInspectionService $platformPaymentsInspectionService,
    ): JsonResponse {
        return response()->json(
            $platformPaymentsInspectionService->inspect($request->validated())
        );
    }
}
