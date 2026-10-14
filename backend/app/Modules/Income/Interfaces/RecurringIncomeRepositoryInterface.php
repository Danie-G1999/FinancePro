<?php

namespace App\Modules\Income\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RecurringIncomeRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId): Collection;

    public function findByUserOrFail(int $userId, int $id): Model;

    /** Ingresos recurrentes activos, de un usuario o de todos. */
    public function getActive(?int $userId = null): Collection;
}
