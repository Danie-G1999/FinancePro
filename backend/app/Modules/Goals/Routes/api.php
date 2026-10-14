<?php

use App\Modules\Goals\Controllers\GoalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('goals', GoalController::class)
        ->parameters(['goals' => 'id']);
});
