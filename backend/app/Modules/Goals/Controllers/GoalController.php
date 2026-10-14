<?php

namespace App\Modules\Goals\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Goals\Requests\StoreGoalRequest;
use App\Modules\Goals\Requests\UpdateGoalRequest;
use App\Modules\Goals\Resources\GoalResource;
use App\Modules\Goals\Services\GoalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GoalController extends Controller
{
    public function __construct(
        private GoalService $service
    ) {}

    public function index(Request $request)
    {
        $goals = $this->service->getUserGoals($request->user()->id);

        return GoalResource::collection($goals);
    }

    public function store(StoreGoalRequest $request): JsonResponse
    {
        $goal = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Meta creada correctamente.',
            'data' => new GoalResource($goal),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return new GoalResource(
            $this->service->findForUser($request->user()->id, $id)
        );
    }

    public function update(UpdateGoalRequest $request, int $id): JsonResponse
    {
        $goal = $this->service->updateForUser(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Meta actualizada correctamente.',
            'data' => new GoalResource($goal),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->deleteForUser($request->user()->id, $id);

        return response()->json([
            'message' => 'Meta eliminada correctamente.',
        ]);
    }
}
