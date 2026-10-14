<?php

namespace App\Modules\Income\Repositories;

use App\Modules\Income\Interfaces\IncomeRepositoryInterface;
use App\Modules\Income\Models\Income;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class IncomeRepository extends BaseRepository implements IncomeRepositoryInterface
{
    public function __construct(Income $model)
    {
        parent::__construct($model);
    }

    public function paginateByUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with(['account:id,name,currency', 'recurringIncome:id,type'])
            ->where('user_id', $userId)
            ->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findByUserOrFail(int $userId, int $id): Model
    {
        return $this->model
            ->newQuery()
            ->with(['account:id,name,currency', 'recurringIncome:id,type'])
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return parent::create($data)->load(['account:id,name,currency', 'recurringIncome:id,type']);
    }

    public function update(int|string $id, array $data): Model
    {
        return parent::update($id, $data)->load(['account:id,name,currency', 'recurringIncome:id,type']);
    }
}
