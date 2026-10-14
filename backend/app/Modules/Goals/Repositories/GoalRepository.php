<?php

namespace App\Modules\Goals\Repositories;

use App\Modules\Goals\Interfaces\GoalRepositoryInterface;
use App\Modules\Goals\Models\Goal;
use App\Shared\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class GoalRepository extends BaseRepository implements GoalRepositoryInterface
{
    public function __construct(Goal $model)
    {
        parent::__construct($model);
    }

    public function getByUser(int $userId): Collection
    {
        return $this->model
            ->newQuery()
            ->withSum('contributions', 'amount')
            ->withCount('contributions')
            ->where('user_id', $userId)
            ->orderByRaw('target_date IS NULL')
            ->orderBy('target_date')
            ->orderBy('name')
            ->get();
    }

    public function findByUserOrFail(int $userId, int $id): Model
    {
        return $this->model
            ->newQuery()
            ->where('user_id', $userId)
            ->findOrFail($id);
    }
}
