<?php

namespace App\Modules\Profile;

use App\Modules\Profile\Interfaces\ProfileRepositoryInterface;
use App\Modules\Profile\Repositories\ProfileRepository;
use Illuminate\Support\ServiceProvider;

class ProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ProfileRepositoryInterface::class,
            ProfileRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );
    }
}
