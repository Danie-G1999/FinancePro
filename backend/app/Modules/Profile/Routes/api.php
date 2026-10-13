<?php

use App\Modules\Profile\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
    Route::post('/photo', [ProfileController::class, 'updatePhoto']);
    Route::delete('/photo', [ProfileController::class, 'destroyPhoto']);
});
