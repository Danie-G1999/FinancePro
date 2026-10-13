<?php

namespace App\Modules\Expenses\Repositories;

use App\Modules\Expenses\Interfaces\ExpenseRepositoryInterface;
use App\Modules\Expenses\Models\Expense;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ExpenseRepository extends BaseRepository implements ExpenseRepositoryInterface
{
    public function __construct(Expense $model)
    {
        parent::__construct($model);
    }

    public function paginateByUser(int $userId, int $perPage): LengthAwarePaginator
    {
        return $this->model
            ->newQuery()
            ->with(['account:id,name,currency', 'debt:id,name'])
            ->where('user_id', $userId)
            ->orderByDesc('spent_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findByUserOrFail(int $userId, int $id): Model
    {
        return $this->model
            ->newQuery()
            ->with(['account:id,name,currency', 'debt:id,name'])
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return parent::create($data)->load(['account:id,name,currency', 'debt:id,name']);
    }

    public function update(int|string $id, array $data): Model
    {
        return parent::update($id, $data)->load(['account:id,name,currency', 'debt:id,name']);
    }
}
