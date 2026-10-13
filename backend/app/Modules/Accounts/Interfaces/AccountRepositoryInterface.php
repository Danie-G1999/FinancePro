<?php

namespace App\Modules\Accounts\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface AccountRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId): Collection;
}
