<?php

use App\Modules\Categories\Controllers\ExpenseCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('expense-categories', ExpenseCategoryController::class)
        ->parameters(['expense-categories' => 'id']);
});
