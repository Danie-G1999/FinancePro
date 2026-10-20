<?php

use App\Modules\Auth\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

// Rutas web (no API): el viaje de ida y vuelta a Google necesita la sesión y la cookie de estado.
Route::middleware(['web', 'throttle:oauth'])->prefix('auth/google')->group(function () {
    Route::get('/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('auth.google.redirect');

    Route::get('/callback', [GoogleAuthController::class, 'callback'])
        ->name('auth.google.callback');
});
