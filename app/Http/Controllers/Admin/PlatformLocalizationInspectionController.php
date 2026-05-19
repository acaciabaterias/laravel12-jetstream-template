<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PlatformLocalizationInspectionRequest;
use App\Services\Platform\PlatformLocaleInspectionService;
use Illuminate\Http\JsonResponse;

class PlatformLocalizationInspectionController extends Controller
{
    public function __invoke(
        PlatformLocalizationInspectionRequest $request,
        PlatformLocaleInspectionService $platformLocaleInspectionService,
    ): JsonResponse {
        return response()->json($platformLocaleInspectionService->inspect($request->validated()));
    }
}
