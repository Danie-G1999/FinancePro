<?php

namespace App\Modules\Categories\Repositories;

use App\Modules\Categories\Interfaces\ExpenseCategoryRepositoryInterface;
use App\Modules\Categories\Models\ExpenseCategory;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategoryRepository extends BaseRepository implements ExpenseCategoryRepositoryInterface
{
    public function __construct(ExpenseCategory $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId, ?string $budgetType = null, bool $onlyActive = false): Collection
    {
        return $this->model
            ->newQuery()
            ->withCount('expenses')
            ->where('user_id', $userId)
            ->when($budgetType, fn ($query) => $query->where('budget_type', $budgetType))
            ->when($onlyActive, fn ($query) => $query->where('is_active', true))
            ->orderByRaw("CASE budget_type WHEN 'need' THEN 1 WHEN 'want' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->get();
    }

    public function findByUserOrFail(int $userId, int $id): Model
    {
        return $this->model
            ->newQuery()
            ->withCount('expenses')
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function firstOrCreateForUser(int $userId, string $name, array $attributes): Model
    {
        return $this->model
            ->newQuery()
            ->firstOrCreate(
                ['user_id' => $userId, 'name' => $name],
                $attributes
            );
    }
}
