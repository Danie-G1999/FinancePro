<?php

namespace App\Modules\Debts\Repositories;

use App\Modules\Debts\Interfaces\DebtRepositoryInterface;
use App\Modules\Debts\Models\Debt;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DebtRepository extends BaseRepository implements DebtRepositoryInterface
{
    public function __construct(Debt $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderBy('name')
            ->get();
    }

    public function findByUserOrFail(int $userId, int $id): Model
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->findOrFail($id);
    }
}
