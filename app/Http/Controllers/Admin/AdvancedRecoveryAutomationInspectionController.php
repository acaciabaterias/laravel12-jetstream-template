<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdvancedRecoveryAutomationInspectionRequest;
use App\Services\Billing\AdvancedRecoveryAutomationInspectionService;
use Illuminate\Http\JsonResponse;

class AdvancedRecoveryAutomationInspectionController extends Controller
{
    public function __invoke(
        AdvancedRecoveryAutomationInspectionRequest $request,
        AdvancedRecoveryAutomationInspectionService $advancedRecoveryAutomationInspectionService,
    ): JsonResponse {
        return response()->json(
            $advancedRecoveryAutomationInspectionService->inspect($request->validated())
        );
    }
}
