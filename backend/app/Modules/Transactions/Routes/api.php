<?php

use App\Modules\Transactions\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('transactions', [TransactionController::class, 'index']);
