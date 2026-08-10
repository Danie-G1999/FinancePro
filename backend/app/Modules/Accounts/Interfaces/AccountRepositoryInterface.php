<?php

namespace App\Modules\Accounts\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Modules\Accounts\Models\Account;

interface AccountRepositoryInterface extends BaseRepositoryInterface
{
    public function allByUser(int $userId): Collection;

    public function findByUser(int $userId, int|string $accountId): ?Account;

    public function findByUserOrFail(int $userId, int|string $accountId): Account;
}
