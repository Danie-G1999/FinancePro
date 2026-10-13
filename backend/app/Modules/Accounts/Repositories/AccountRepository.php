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

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }
}
