<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Shared\Services\BaseService;

class AccountService extends BaseService
{
    public function __construct(
        AccountRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getUserAccounts(int $userId)
    {
        return $this->repository->allByUser($userId);
    }

    public function getUserAccount(
        int $userId,
        int|string $accountId
    ) {
        return $this->repository->findByUserOrFail(
            $userId,
            $accountId
        );
    }

    public function createUserAccount(
        int $userId,
        array $data
    ) {
        $data['user_id'] = $userId;

        return $this->repository->create($data);
    }

    public function updateUserAccount(
        int $userId,
        int|string $accountId,
        array $data
    ) {
        $account = $this->repository->findByUserOrFail(
            $userId,
            $accountId
        );

        $account->update($data);

        return $account->refresh();
    }

    public function deleteUserAccount(
        int $userId,
        int|string $accountId
    ): bool {
        $account = $this->repository->findByUserOrFail(
            $userId,
            $accountId
        );

        return (bool) $account->delete();
    }
}
