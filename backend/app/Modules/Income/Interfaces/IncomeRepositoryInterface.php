<?php

namespace App\Modules\Income\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface IncomeRepositoryInterface extends BaseRepositoryInterface
{
    public function paginateByUser(int $userId, int $perPage): LengthAwarePaginator;

    public function findByUserOrFail(int $userId, int $id): Model;
}
