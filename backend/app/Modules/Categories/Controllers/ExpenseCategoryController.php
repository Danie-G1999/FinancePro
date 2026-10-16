<?php

namespace App\Modules\Categories\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Budgets\Enums\BudgetType;
use App\Modules\Categories\Requests\StoreExpenseCategoryRequest;
use App\Modules\Categories\Requests\UpdateExpenseCategoryRequest;
use App\Modules\Categories\Resources\ExpenseCategoryResource;
use App\Modules\Categories\Services\ExpenseCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseCategoryController extends Controller
{
    public function __construct(
        private ExpenseCategoryService $service
    ) {}

    public function index(Request $request)
    {
        $filters = $request->validate([
            'budget_type' => ['nullable', Rule::enum(BudgetType::class)],
            'active' => ['nullable', 'boolean'],
        ]);

        $categories = $this->service->getUserCategories(
            $request->user()->id,
            $filters['budget_type'] ?? null,
            (bool) ($filters['active'] ?? false)
        );

        return ExpenseCategoryResource::collection($categories);
    }

    public function store(StoreExpenseCategoryRequest $request): JsonResponse
    {
        $category = $this->service->createForUser(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Categoría creada correctamente.',
            'data' => new ExpenseCategoryResource($category),
        ], 201);
    }

    public function show(Request $request, int $id)
    {
        return new ExpenseCategoryResource(
            $this->service->findForUser($request->user()->id, $id)
        );
    }

    public function update(UpdateExpenseCategoryRequest $request, int $id): JsonResponse
    {
        $category = $this->service->updateForUser(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data' => new ExpenseCategoryResource($category),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->service->deleteForUser($request->user()->id, $id);

        return response()->json([
            'message' => $deleted
                ? 'Categoría eliminada correctamente.'
                : 'La categoría tiene gastos registrados, así que se desactivó en lugar de eliminarse.',
            'deleted' => $deleted,
        ]);
    }
}
