<?php

use App\Modules\Budgets\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('budgets')->group(function () {
    Route::get('/', [BudgetController::class, 'show']);
    Route::put('/', [BudgetController::class, 'save']);
    Route::delete('/', [BudgetController::class, 'reset']);
});
