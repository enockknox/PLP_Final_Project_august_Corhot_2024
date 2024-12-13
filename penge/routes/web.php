<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get('/login', [AuthController::class, 'login']);


Route::get('/', function () {
    return view('welcome');
});
