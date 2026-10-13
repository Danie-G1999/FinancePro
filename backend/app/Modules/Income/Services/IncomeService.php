<?php

namespace App\Modules\Income\Services;

use App\Modules\Income\Interfaces\IncomeRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class IncomeService extends BaseService
{
    public function __construct(
        IncomeRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public const PER_PAGE = 20;

    public function getUserIncomes(int $userId): LengthAwarePaginator
    {
        return $this->repository->paginateByUser($userId, self::PER_PAGE);
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
        $income = $this->findForUser($userId, $id);

        return $this->repository->update($income->getKey(), $data);
    }

    public function deleteForUser(int $userId, int $id): bool
    {
        $income = $this->findForUser($userId, $id);

        return $this->repository->delete($income->getKey());
    }
}
