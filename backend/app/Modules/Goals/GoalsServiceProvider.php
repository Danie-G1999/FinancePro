<?php

namespace App\Modules\Goals;

use App\Modules\Goals\Interfaces\GoalRepositoryInterface;
use App\Modules\Goals\Repositories\GoalRepository;
use Illuminate\Support\ServiceProvider;

class GoalsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            GoalRepositoryInterface::class,
            GoalRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
