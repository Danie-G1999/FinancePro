<?php

namespace App\Modules\Profile\Repositories;

use App\Modules\Profile\Interfaces\ProfileRepositoryInterface;
use App\Modules\Profile\Models\Profile;

class ProfileRepository implements ProfileRepositoryInterface
{
    public function __construct(
        private Profile $model
    ) {}

    public function findByUser(int $userId): ?Profile
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->first();
    }

    public function updateOrCreateForUser(int $userId, array $data): Profile
    {
        return $this->model
            ->newQuery()
            ->updateOrCreate(['user_id' => $userId], $data);
    }
}
