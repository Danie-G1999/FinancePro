<?php

namespace App\Modules\Categories;

use App\Models\User;
use App\Modules\Categories\Interfaces\ExpenseCategoryRepositoryInterface;
use App\Modules\Categories\Repositories\ExpenseCategoryRepository;
use App\Modules\Categories\Services\ExpenseCategoryService;
use Illuminate\Support\ServiceProvider;

class CategoriesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ExpenseCategoryRepositoryInterface::class,
            ExpenseCategoryRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/database/migrations'
        );

        // Cada usuario nuevo arranca con las categorías sugeridas de la regla 50/30/20.
        User::created(function (User $user) {
            $this->app->make(ExpenseCategoryService::class)
                ->createDefaultsForUser($user->id);
        });
    }
}
