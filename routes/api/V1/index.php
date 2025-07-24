<?php

use App\Http\Controllers\Api\V1\DepositController;
use App\Http\Controllers\Api\V1\TransferController;
use App\Http\Controllers\Api\V1\WithdrawalController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [AuthController::class, 'me'])->name('me');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::prefix('wallet')->name('wallet.')->group(function () {
    Route::middleware(['auth:sanctum', 'throttle:5,1'])->group(function () {
        Route::post('deposit', [DepositController::class, 'store'])->name('deposit');
        Route::post('/withdrawals', [WithdrawalController::class, 'store'])->name('withdrawals');
        Route::post('/transfer', [TransferController::class, 'store'])
            ->middleware('idempotent')->name('transfer');
    });
});
