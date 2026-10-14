<?php

namespace App\Modules\Income\Repositories;

use App\Modules\Income\Interfaces\RecurringIncomeRepositoryInterface;
use App\Modules\Income\Models\RecurringIncome;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RecurringIncomeRepository extends BaseRepository implements RecurringIncomeRepositoryInterface
{
    private const WITH = 'account:id,name,currency,is_active';

    public function __construct(RecurringIncome $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->with(self::WITH)
            ->where('user_id', $userId)
            ->orderByDesc('is_active')
            ->orderBy('day_of_month')
            ->orderBy('description')
            ->get();
    }

    public function findByUserOrFail(int $userId, int $id): Model
    {
        return $this->model
            ->newQuery()
            ->with(self::WITH)
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function getActive(?int $userId = null): Collection
    {
        return $this->model
            ->newQuery()
            ->with(self::WITH)
            ->where('is_active', true)
            ->when($userId, fn ($query, int $id) => $query->where('user_id', $id))
            ->get();
    }

    public function create(array $data): Model
    {
        return parent::create($data)->load(self::WITH);
    }

    public function update(int|string $id, array $data): Model
    {
        return parent::update($id, $data)->load(self::WITH);
    }
}
