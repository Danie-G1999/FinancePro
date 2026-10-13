<?php

namespace App\Modules\Accounts\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AccountRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId): Collection;

    public function findByUserOrFail(int $userId, int $id): Model;
}
