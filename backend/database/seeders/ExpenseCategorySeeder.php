<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Categories\Services\ExpenseCategoryService;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /** Completa las categorías sugeridas a los usuarios que existían antes del módulo. */
    public function run(ExpenseCategoryService $service): void
    {
        User::query()->pluck('id')->each(
            fn (int $userId) => $service->createDefaultsForUser($userId)
        );
    }
}
