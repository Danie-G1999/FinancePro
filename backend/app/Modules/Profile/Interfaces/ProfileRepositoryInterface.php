<?php

namespace App\Modules\Profile\Interfaces;

use App\Modules\Profile\Models\Profile;

interface ProfileRepositoryInterface
{
    public function findByUser(int $userId): ?Profile;

    public function updateOrCreateForUser(int $userId, array $data): Profile;
}
