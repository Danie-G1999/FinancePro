<?php

namespace App\Modules\Reports\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reports\Requests\ReportRequest;
use App\Modules\Reports\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $service
    ) {}

    public function index(ReportRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'data' => $this->service->getReport(
                $request->user()->id,
                $data['date_from'],
                $data['date_to'],
                $data['currency'] ?? null
            ),
        ]);
    }
}
