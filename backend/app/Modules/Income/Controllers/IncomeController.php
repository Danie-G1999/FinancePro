<?php

namespace App\Modules\Income\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Income\Requests\StoreIncomeRequest;
use App\Modules\Income\Requests\UpdateIncomeRequest;
use App\Modules\Income\Resources\IncomeResource;
use App\Modules\Income\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function __construct(
        private IncomeService $service
    ) {}

    public function index(Request $request)
    {
        $incomes = $this->service->getUserIncomes(
            $request->user()->id
        );

        return IncomeResource::collection($incomes);
    }

    public function store(StoreIncomeRequest $request): JsonResponse
    {
        $income = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Ingreso registrado correctamente.',
            'data' => new IncomeResource($income),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $income = $this->service->findForUser(
            $request->user()->id,
            $id
        );

        return new IncomeResource($income);
    }

    public function update(UpdateIncomeRequest $request, int $id): JsonResponse
    {
        $income = $this->service->updateForUser(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Ingreso actualizado correctamente.',
            'data' => new IncomeResource($income),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteForUser(
            $request->user()->id,
            $id
        );

        return response()->json([
            'message' => 'Ingreso eliminado correctamente.',
        ]);
    }
}
