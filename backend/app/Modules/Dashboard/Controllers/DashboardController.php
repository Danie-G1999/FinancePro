<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Dashboard\Requests\DashboardFilterRequest;
use App\Modules\Dashboard\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $service
    ) {}

    public function index(DashboardFilterRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getSummary(
                $request->user()->id,
                $request->validated('period'),
                $request->validated('account_id')
            ),
        ]);
    }
}
