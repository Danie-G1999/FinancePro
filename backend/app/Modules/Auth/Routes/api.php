<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Auth\Controllers\GoogleAuthController;

Route::prefix('auth')->group(function () {

    Route::post('/register', [
        AuthController::class,
        'register',
    ])->middleware('throttle:register');

    Route::get('/providers', [
        GoogleAuthController::class,
        'providers',
    ]);

    Route::post('/login', [
        AuthController::class,
        'login',
    ])->middleware('throttle:login');

    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/me', [
            AuthController::class,
            'me',
        ]);

        Route::post('/logout', [
            AuthController::class,
            'logout',
        ]);

        Route::post('/onboarding/complete', [
            AuthController::class,
            'completeOnboarding',
        ]);
    });
});
