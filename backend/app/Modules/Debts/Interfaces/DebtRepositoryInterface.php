<?php

namespace App\Modules\Debts\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface DebtRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId): Collection;

    public function findByUserOrFail(int $userId, int $id): Model;

    public function getPayments(int $debtId): Collection;
}
