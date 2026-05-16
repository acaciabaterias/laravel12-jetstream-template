<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformBillingInspectionRequest;
use App\Services\Billing\PlatformBillingInspectionService;
use Illuminate\Http\JsonResponse;

class PlatformBillingInspectionController extends Controller
{
    public function __invoke(
        PlatformBillingInspectionRequest $request,
        PlatformBillingInspectionService $platformBillingInspectionService,
    ): JsonResponse {
        $payload = $platformBillingInspectionService->inspect($request->validated());

        return response()->json($payload);
    }
}
