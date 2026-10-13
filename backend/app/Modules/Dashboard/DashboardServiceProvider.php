<?php

namespace App\Modules\Dashboard;

use App\Modules\Dashboard\Interfaces\DashboardRepositoryInterface;
use App\Modules\Dashboard\Repositories\DashboardRepository;
use Illuminate\Support\ServiceProvider;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            DashboardRepositoryInterface::class,
            DashboardRepository::class
        );
    }
}
