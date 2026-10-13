<?php

use App\Modules\Expenses\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('expenses', ExpenseController::class)
        ->parameters(['expenses' => 'id']);
});
