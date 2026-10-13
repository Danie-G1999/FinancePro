<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AccountService extends BaseService
{
    public function __construct(
        AccountRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getUserAccounts(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function createForUser(
        int $userId,
        array $data
    ): Model {
        $data['user_id'] = $userId;

        return $this->repository->create($data);
    }
}
