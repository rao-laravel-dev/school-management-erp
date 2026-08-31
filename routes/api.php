<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeeApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public route (login ke liye token chahiye nahi) — throttle: brute-force protection
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// Protected routes (in sabko valid Sanctum token chahiye hoga) + rate limit
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    // Fee API routes
    Route::prefix('fees')->name('api.fees.')->group(function () {
        Route::middleware(['can:access-fee-collections'])->group(function () {
            Route::controller(FeeApiController::class)->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/{studentFeeId}', 'show')->name('show');
            });
        });

        Route::middleware(['can:manage-fee-collections'])->group(function () {
            Route::controller(FeeApiController::class)->group(function () {
                Route::post('/{studentFeeId}/pay', 'pay')->name('pay');
            });
        });
    });

    // 👇 Fee API routes yahan aage add karenge
});