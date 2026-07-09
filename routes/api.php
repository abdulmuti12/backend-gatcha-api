<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Admin\GachaEventController;
use App\Http\Controllers\Api\Admin\GachaHistoryController as AdminGachaHistoryController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\GachaController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| USER AUTH (guard: api)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

/*
|--------------------------------------------------------------------------
| USER FEATURES (guard: api)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {
    Route::get('profile', [ProfileController::class, 'show']);
    Route::get('profile/histories', [ProfileController::class, 'histories']);

    Route::get('gacha-events', [GachaController::class, 'index']);
    Route::get('gacha-events/{event}', [GachaController::class, 'show']);
    Route::post('gacha-events/{event}/pull', [GachaController::class, 'pull']);
});

/*
|--------------------------------------------------------------------------
| ADMIN AUTH (guard: admin-api)
|--------------------------------------------------------------------------
*/
Route::prefix('admin/auth')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);

    Route::middleware('auth:admin-api')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN FEATURES (guard: admin-api)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth:admin-api')->group(function () {
    Route::get('dashboard/stats', [DashboardController::class, 'stats']);
    Route::apiResource('gacha-events', GachaEventController::class)->except(['update']);
    Route::put('gacha-events/{event}', [GachaEventController::class, 'update']);
    Route::put('gacha-events/{event}/items', [GachaEventController::class, 'updateItems']);

    Route::get('gacha-histories', [AdminGachaHistoryController::class, 'index']);
});
