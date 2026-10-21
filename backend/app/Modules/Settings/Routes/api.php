<?php

use App\Modules\Settings\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('user/settings')->group(function () {
    Route::get('/', [UserSettingsController::class, 'show']);
    Route::put('/', [UserSettingsController::class, 'update']);
});

// Actividad en la interfaz (mouse, teclado) sin peticiones a la API: renueva la sesión
// para que el cierre por inactividad del servidor no la dé por abandonada.
Route::middleware('auth:sanctum')->post('user/session/heartbeat', [UserSettingsController::class, 'heartbeat']);
