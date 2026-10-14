<?php

namespace App\Modules\Income\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Income\Requests\SetRecurringIncomeStatusRequest;
use App\Modules\Income\Requests\StoreRecurringIncomeRequest;
use App\Modules\Income\Requests\UpdateRecurringIncomeRequest;
use App\Modules\Income\Resources\RecurringIncomeResource;
use App\Modules\Income\Services\RecurringIncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecurringIncomeController extends Controller
{
    public function __construct(
        private RecurringIncomeService $service
    ) {}

    public function index(Request $request)
    {
        return RecurringIncomeResource::collection(
            $this->service->getUserSchedules($request->user()->id)
        );
    }

    public function store(StoreRecurringIncomeRequest $request): JsonResponse
    {
        $schedule = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Ingreso recurrente creado correctamente.',
            'data' => new RecurringIncomeResource($schedule),
        ], 201);
    }

    public function update(UpdateRecurringIncomeRequest $request, int $id): JsonResponse
    {
        $schedule = $this->service->updateForUser(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Ingreso recurrente actualizado correctamente.',
            'data' => new RecurringIncomeResource($schedule),
        ]);
    }

    public function updateStatus(SetRecurringIncomeStatusRequest $request, int $id): JsonResponse
    {
        $schedule = $this->service->setActive(
            $request->user()->id,
            $id,
            $request->boolean('is_active')
        );

        return response()->json([
            'message' => $schedule->is_active
                ? 'Ingreso recurrente activado.'
                : 'Ingreso recurrente en pausa.',
            'data' => new RecurringIncomeResource($schedule),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteForUser($request->user()->id, $id);

        return response()->json([
            'message' => 'Ingreso recurrente eliminado correctamente.',
        ]);
    }
}
