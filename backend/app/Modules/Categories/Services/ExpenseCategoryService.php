<?php

namespace App\Modules\Categories\Services;

use App\Modules\Categories\Interfaces\ExpenseCategoryRepositoryInterface;
use App\Modules\Categories\Support\DefaultExpenseCategories;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ExpenseCategoryService extends BaseService
{
    public function __construct(
        ExpenseCategoryRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getUserCategories(int $userId, ?string $budgetType = null, bool $onlyActive = false): Collection
    {
        return $this->repository->getByUser($userId, $budgetType, $onlyActive);
    }

    public function createForUser(int $userId, array $data): Model
    {
        $data['user_id'] = $userId;

        return $this->repository->create($data)->loadCount('expenses');
    }

    public function findForUser(int $userId, int $id): Model
    {
        return $this->repository->findByUserOrFail($userId, $id);
    }

    public function updateForUser(int $userId, int $id, array $data): Model
    {
        $category = $this->findForUser($userId, $id);

        return $this->repository->update($category->getKey(), $data)->loadCount('expenses');
    }

    /**
     * Las categorías con gastos se desactivan para no perder la clasificación del historial.
     *
     * @return bool true si se eliminó, false si solo se desactivó.
     */
    public function deleteForUser(int $userId, int $id): bool
    {
        $category = $this->findForUser($userId, $id);

        if ($category->expenses_count > 0) {
            $this->repository->update($category->getKey(), ['is_active' => false]);

            return false;
        }

        return $this->repository->delete($category->getKey());
    }

    /** Idempotente: no duplica ni reactiva categorías que el usuario ya tenga. */
    public function createDefaultsForUser(int $userId): void
    {
        foreach (DefaultExpenseCategories::all() as $budgetType => $categories) {
            foreach ($categories as $category) {
                $this->repository->firstOrCreateForUser($userId, $category['name'], [
                    'budget_type' => $budgetType,
                    'icon' => $category['icon'],
                    'color' => DefaultExpenseCategories::COLORS[$budgetType],
                ]);
            }
        }
    }
}
