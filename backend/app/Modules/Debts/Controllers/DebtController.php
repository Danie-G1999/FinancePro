<?php

namespace App\Modules\Debts\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Debts\Requests\StoreDebtRequest;
use App\Modules\Debts\Requests\UpdateDebtRequest;
use App\Modules\Debts\Resources\DebtResource;
use App\Modules\Debts\Services\DebtService;
use App\Modules\Expenses\Resources\ExpenseResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    public function __construct(
        private DebtService $service
    ) {}

    public function index(Request $request)
    {
        $debts = $this->service->getUserDebts(
            $request->user()->id
        );

        return DebtResource::collection($debts);
    }

    public function store(StoreDebtRequest $request): JsonResponse
    {
        $debt = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Deuda creada correctamente.',
            'data' => new DebtResource($debt),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        $debt = $this->service->findForUser(
            $request->user()->id,
            $id
        );

        return new DebtResource($debt);
    }

    public function payments(Request $request, int $id)
    {
        $payments = $this->service->getPayments(
            $request->user()->id,
            $id
        );

        return ExpenseResource::collection($payments);
    }

    public function update(UpdateDebtRequest $request, int $id): JsonResponse
    {
        $debt = $this->service->updateForUser(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Deuda actualizada correctamente.',
            'data' => new DebtResource($debt),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteForUser(
            $request->user()->id,
            $id
        );

        return response()->json([
            'message' => 'Deuda eliminada correctamente.',
        ]);
    }
}
