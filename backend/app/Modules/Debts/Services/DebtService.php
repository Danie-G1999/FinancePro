<?php

namespace App\Modules\Debts\Services;

use App\Modules\Debts\Enums\PaymentFrequency;
use App\Modules\Debts\Interfaces\DebtRepositoryInterface;
use App\Shared\Services\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DebtService extends BaseService
{
    public function __construct(
        DebtRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    public function getUserDebts(int $userId): Collection
    {
        return $this->repository->getByUser($userId);
    }

    public function createForUser(
        int $userId,
        array $data
    ): Model {
        $data['user_id'] = $userId;
        $data = $this->normalizeSchedule($data);

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
        $debt = $this->findForUser($userId, $id);

        $data = $this->normalizeSchedule($data);

        return $this->repository->update($debt->getKey(), $data);
    }

    public function deleteForUser(int $userId, int $id): bool
    {
        $debt = $this->findForUser($userId, $id);

        return $this->repository->delete($debt->getKey());
    }

    private function normalizeSchedule(array $data): array
    {
        if ($data['payment_frequency'] === PaymentFrequency::MONTHLY->value) {
            $data['second_payment_day'] = null;
        }

        return $data;
    }
}
