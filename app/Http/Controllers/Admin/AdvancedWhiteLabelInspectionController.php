<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvancedWhiteLabelInspectionRequest;
use App\Services\Operations\AdvancedWhiteLabelInspectionService;
use Illuminate\Http\JsonResponse;

class AdvancedWhiteLabelInspectionController extends Controller
{
    public function __invoke(
        AdvancedWhiteLabelInspectionRequest $request,
        AdvancedWhiteLabelInspectionService $advancedWhiteLabelInspectionService,
    ): JsonResponse {
        return response()->json(
            $advancedWhiteLabelInspectionService->inspect($request->validated())
        );
    }
}
