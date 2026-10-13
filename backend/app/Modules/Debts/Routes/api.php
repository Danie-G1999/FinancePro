<?php

use App\Modules\Debts\Controllers\DebtController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('debts/{id}/payments', [DebtController::class, 'payments']);

    Route::apiResource('debts', DebtController::class)
        ->parameters(['debts' => 'id']);
});
