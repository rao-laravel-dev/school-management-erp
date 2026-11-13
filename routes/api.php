<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeeApiController;
use App\Http\Controllers\Api\FeeVoucherApiController;
use App\Http\Middleware\EnsureApiUserIsActive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public route (login ke liye token chahiye nahi) — throttle: brute-force protection
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

// Protected routes (in sabko valid Sanctum token chahiye hoga) + rate limit
Route::middleware(['auth:sanctum', EnsureApiUserIsActive::class, 'throttle:60,1'])->group(function () {
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

    // Portal fee vouchers (read-only, own data only; portal permissions, not admin fee permission)
    Route::prefix('parent/fees')->name('api.parent.fees.')->middleware('can:access-parent-fees')
        ->controller(FeeVoucherApiController::class)->group(function () {
            Route::get('/vouchers', 'parentVouchers')->name('vouchers');
            Route::get('/vouchers/{voucher}/pdf', 'parentVoucherPdf')->whereNumber('voucher')->name('vouchers.pdf');
        });
    Route::prefix('student/fees')->name('api.student.fees.')->middleware('can:access-student-fees')
        ->controller(FeeVoucherApiController::class)->group(function () {
            Route::get('/vouchers', 'studentVouchers')->name('vouchers');
            Route::get('/vouchers/{voucher}/pdf', 'studentVoucherPdf')->whereNumber('voucher')->name('vouchers.pdf');
        });

    // 👇 Fee API routes yahan aage add karenge
});