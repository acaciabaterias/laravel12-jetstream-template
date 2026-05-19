<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PlatformFiscalRuleInspectionRequest;
use App\Services\Fiscal\PlatformFiscalInspectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PlatformFiscalRuleInspectionController
{
    public function __invoke(
        PlatformFiscalRuleInspectionRequest $request,
        PlatformFiscalInspectionService $platformFiscalInspectionService,
    ): JsonResponse {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-fiscal-rules');

        return response()->json(
            $platformFiscalInspectionService->inspect($request->validated())
        );
    }
}
