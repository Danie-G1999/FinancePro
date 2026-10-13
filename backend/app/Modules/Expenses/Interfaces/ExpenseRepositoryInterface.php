<?php

namespace App\Modules\Expenses\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ExpenseRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage): LengthAwarePaginator;

    public function findByUserOrFail(int $userId, int $id): Model;
}
