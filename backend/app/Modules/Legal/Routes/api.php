<?php

use App\Modules\Legal\Controllers\LegalController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('legal')->group(function () {
    Route::post('/accept', [LegalController::class, 'accept']);
});
