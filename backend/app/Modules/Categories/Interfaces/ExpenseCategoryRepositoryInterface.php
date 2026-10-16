<?php

namespace App\Modules\Categories\Interfaces;

use App\Shared\Repositories\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ExpenseCategoryRepositoryInterface extends BaseRepositoryInterface
{
    public function getByUser(int $userId, ?string $budgetType = null, bool $onlyActive = false): Collection;

    public function findByUserOrFail(int $userId, int $id): Model;

    public function firstOrCreateForUser(int $userId, string $name, array $attributes): Model;
}
