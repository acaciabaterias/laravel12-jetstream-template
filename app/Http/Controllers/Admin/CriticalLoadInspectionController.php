<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CriticalLoadInspectionRequest;
use App\Services\Operations\CriticalLoadInspectionService;
use Illuminate\Http\JsonResponse;

class CriticalLoadInspectionController extends Controller
{
    public function __invoke(
        CriticalLoadInspectionRequest $request,
        CriticalLoadInspectionService $criticalLoadInspectionService,
    ): JsonResponse {
        return response()->json(
            $criticalLoadInspectionService->inspect($request->validated())
        );
    }
}
