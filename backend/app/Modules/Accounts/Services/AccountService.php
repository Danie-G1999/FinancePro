<?php

namespace App\Modules\Accounts\Services;

use App\Modules\Accounts\Interfaces\AccountRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

    public function findForUser(int $userId, int $id): Model
    {
        return $this->repository->findByUserOrFail($userId, $id);
    }

    public function updateForUser(
        int $userId,
        int $id,
        array $data
    ): Model {
        $account = $this->findForUser($userId, $id);

        $this->ensureCurrencyCanChange(
            $account,
            $data,
            'No puedes cambiar la moneda: esta cuenta ya tiene movimientos o ingresos programados.'
        );

        return $this->repository->update($account->getKey(), $data);
    }

    public function deleteForUser(int $userId, int $id): bool
    {
        $account = $this->findForUser($userId, $id);

        return $this->repository->delete($account->getKey());
    }

    /** La moneda solo se puede corregir mientras no haya datos que dependan de ella. */
    private function ensureCurrencyCanChange(Model $model, array $data, string $message): void
    {
        if (
            array_key_exists('currency', $data)
            && strtoupper((string) $data['currency']) !== $model->currency
            && $model->currencyLocked()
        ) {
            throw ValidationException::withMessages(['currency' => $message]);
        }
    }
}
