<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

require base_path('app/Modules/Auth/Routes/api.php');
require base_path('app/Modules/Accounts/Routes/api.php');
require base_path('app/Modules/Debts/Routes/api.php');
require base_path('app/Modules/Income/Routes/api.php');
require base_path('app/Modules/Expenses/Routes/api.php');
require base_path('app/Modules/Dashboard/Routes/api.php');
require base_path('app/Modules/Transactions/Routes/api.php');
require base_path('app/Modules/Profile/Routes/api.php');
