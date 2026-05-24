<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformFiscalRuleInspectionRequest;
use App\Services\Fiscal\PlatformFiscalScenarioLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class PlatformFiscalRuleResolutionController extends Controller
{
    public function __invoke(
        PlatformFiscalRuleInspectionRequest $request,
        PlatformFiscalScenarioLookupService $platformFiscalScenarioLookupService,
    ): JsonResponse {
        Gate::forUser(auth('platform')->user())->authorize('view-platform-fiscal-rules');

        $validated = $request->validated();
        $scenarioKey = (string) ($validated['scenario'] ?? '');

        return response()->json(
            $platformFiscalScenarioLookupService->consumerContract($scenarioKey, null, $validated)
        );
    }
}
