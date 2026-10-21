<?php

use App\Modules\Settings\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('user/settings')->group(function () {
    Route::get('/', [UserSettingsController::class, 'show']);
    Route::put('/', [UserSettingsController::class, 'update']);
});
