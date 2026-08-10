<?php

namespace App\Modules\Accounts\Repositories;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Modules\Accounts\Models\Account;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    public function allByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function findByUser(
        int $userId,
        int|string $accountId
    ): ?Account {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->find($accountId);
    }

    public function findByUserOrFail(
        int $userId,
        int|string $accountId
    ): Account {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->findOrFail($accountId);
    }
}
