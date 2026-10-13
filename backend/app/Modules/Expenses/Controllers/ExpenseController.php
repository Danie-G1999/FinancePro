<?php

namespace App\Modules\Expenses\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Expenses\Requests\StoreExpenseRequest;
use App\Modules\Expenses\Requests\UpdateExpenseRequest;
use App\Modules\Expenses\Resources\ExpenseResource;
use App\Modules\Expenses\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(
        private ExpenseService $service
    ) {}

    public function index(Request $request)
    {
        $expenses = $this->service->getUserExpenses(
            $request->user()->id
        );

        return ExpenseResource::collection($expenses);
    }

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        $expense = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Gasto registrado correctamente.',
            'data' => new ExpenseResource($expense),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $expense = $this->service->findForUser(
            $request->user()->id,
            $id
        );

        return new ExpenseResource($expense);
    }

    public function update(UpdateExpenseRequest $request, int $id): JsonResponse
    {
        $expense = $this->service->updateForUser(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Gasto actualizado correctamente.',
            'data' => new ExpenseResource($expense),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteForUser(
            $request->user()->id,
            $id
        );

        return response()->json([
            'message' => 'Gasto eliminado correctamente.',
        ]);
    }
}
