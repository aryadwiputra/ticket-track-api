<?php

use App\Http\Controllers\Api\V1\Master\CategoryController;
use App\Http\Controllers\Api\V1\Master\TicketController;
use App\Http\Controllers\Api\V1\Users\PermissionController;
use App\Http\Controllers\Api\V1\Users\RoleController;
use App\Http\Controllers\Api\V1\Users\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::name('api.v1.')->prefix('v1')->group(function () {

        Route::post('login', App\Http\Controllers\Api\V1\LoginController::class)->name('login');

        Route::post('register', App\Http\Controllers\Api\V1\RegisterController::class)->name('register');

        Route::middleware('auth:sanctum')->group(function () {
            Route::resource('users', UserController::class)
                ->only(['index', 'store', 'show', 'update', 'destroy']);
            Route::get('/users/activity-log', [UserController::class, 'activityLog'])->name('users.activity-log');

            Route::resource('permissions', PermissionController::class)->except(['create', 'edit']);
            Route::resource('roles', RoleController::class)->except(['create', 'edit']);

            Route::resource('categories', CategoryController::class)
                ->except(['create', 'edit']);

            // Ticket Routes
            Route::apiResource('tickets', TicketController::class)->except(['create', 'edit']);
            Route::get('tickets/activity-log', [TicketController::class, 'activityLog']);
        });
    });
});