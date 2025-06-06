<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::post('/login', App\Http\Controllers\Api\V1\LoginController::class)
        ->name('api.login');
    Route::post('/register', App\Http\Controllers\Api\V1\RegisterController::class)
        ->name('api.register');
});