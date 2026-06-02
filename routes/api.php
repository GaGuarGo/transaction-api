<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {
    Route::post('/users/signup', [UserController::class, 'signup']);
    Route::post('/users/signin', [AuthController::class, 'signin']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/refresh', [AuthController::class, 'refresh']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/validate', [AuthController::class, 'validate']);

    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/me/balance', [UserController::class, 'balance']);
    Route::get('/users/me/transactions', [TransactionController::class, 'history']);
    Route::put('/users/me', [UserController::class, 'update']);
    Route::delete('/users/me', [UserController::class, 'destroy']);

    Route::post('/transfer', [TransactionController::class, 'transfer']);
});
