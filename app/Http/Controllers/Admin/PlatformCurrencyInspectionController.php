<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PlatformCurrencyInspectionRequest;
use App\Services\Platform\PlatformCurrencyInspectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PlatformCurrencyInspectionController
{
    public function __invoke(
        PlatformCurrencyInspectionRequest $request,
        PlatformCurrencyInspectionService $platformCurrencyInspectionService,
    ): JsonResponse {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-currencies');

        return response()->json(
            $platformCurrencyInspectionService->inspect($request->validated())
        );
    }
}
