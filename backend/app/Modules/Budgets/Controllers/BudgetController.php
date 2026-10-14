<?php

namespace App\Modules\Budgets\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Budgets\Requests\BudgetPeriodRequest;
use App\Modules\Budgets\Requests\SaveBudgetRequest;
use App\Modules\Budgets\Services\BudgetService;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(
        private BudgetService $service
    ) {}

    public function show(BudgetPeriodRequest $request): JsonResponse
    {
        return response()->json([
            'data' => $this->service->getOverview(
                $request->user()->id,
                $request->validated('period')
            ),
        ]);
    }

    public function save(SaveBudgetRequest $request): JsonResponse
    {
        $data = $request->validated();

        return response()->json([
            'message' => 'Presupuesto guardado correctamente.',
            'data' => $this->service->save(
                $request->user()->id,
                $data['period'],
                ['net_income' => $data['net_income'], 'currency' => $data['currency']]
            ),
        ]);
    }

    public function reset(BudgetPeriodRequest $request): JsonResponse
    {
        return response()->json([
            'message' => 'El presupuesto volvió a usar tus ingresos registrados.',
            'data' => $this->service->reset(
                $request->user()->id,
                $request->validated('period')
            ),
        ]);
    }
}
