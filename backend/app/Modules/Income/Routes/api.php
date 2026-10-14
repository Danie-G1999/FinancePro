<?php

use App\Modules\Income\Controllers\IncomeController;
use App\Modules\Income\Controllers\RecurringIncomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('incomes', IncomeController::class)
        ->parameters(['incomes' => 'id']);

    Route::apiResource('recurring-incomes', RecurringIncomeController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['recurring-incomes' => 'id']);

    Route::patch('recurring-incomes/{id}/status', [RecurringIncomeController::class, 'updateStatus']);
});
