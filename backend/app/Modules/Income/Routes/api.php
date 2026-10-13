<?php

use App\Modules\Income\Controllers\IncomeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('incomes', IncomeController::class)
        ->parameters(['incomes' => 'id']);
});
