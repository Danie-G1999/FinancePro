<?php

namespace App\Modules\Goals\Services;

use App\Modules\Goals\Interfaces\GoalRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class GoalService extends BaseService
{
    public function __construct(
        GoalRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getUserGoals(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function createForUser(int $userId, array $data): Model
    {
        $data['user_id'] = $userId;

        return $this->repository->create($data);
    }

    public function findForUser(int $userId, int $id): Model
    {
        return $this->repository->findByUserOrFail($userId, $id);
    }

    public function updateForUser(int $userId, int $id, array $data): Model
    {
        $goal = $this->findForUser($userId, $id);

        $this->ensureCurrencyCanChange(
            $goal,
            $data,
            'No puedes cambiar la moneda: esta meta ya tiene aportes.'
        );

        return $this->repository->update($goal->getKey(), $data);
    }

    public function deleteForUser(int $userId, int $id): bool
    {
        $goal = $this->findForUser($userId, $id);

        return $this->repository->delete($goal->getKey());
    }

    /** La moneda solo se puede corregir mientras no haya datos que dependan de ella. */
    private function ensureCurrencyCanChange(Model $model, array $data, string $message): void
    {
        if (
            array_key_exists('currency', $data)
            && strtoupper((string) $data['currency']) !== $model->currency
            && $model->currencyLocked()
        ) {
            throw ValidationException::withMessages(['currency' => $message]);
        }
    }
}
