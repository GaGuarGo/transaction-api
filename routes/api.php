<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WalletController;
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
    Route::put('/users/me', [UserController::class, 'update']);
    Route::delete('/users/me', [UserController::class, 'destroy']);

    Route::get('/wallets', [WalletController::class, 'index']);
    Route::post('/wallets', [WalletController::class, 'store']);
    Route::get('/wallets/{id}', [WalletController::class, 'show']);

    Route::get('/users/me/transactions', [TransactionController::class, 'history']);
    Route::post('/transfer', [TransactionController::class, 'transfer']);
});
