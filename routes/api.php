<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Jobs\JobOpeningController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        // ===== ROTAS PUBLICAS =====
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        // ===== ROTAS PROTEGIDAS =====
        Route::middleware('auth:sanctum')->group(function () {
            Route::delete('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::prefix('job-openings')->group(function () {
        // ===== ROTAS PUBLICAS =====
        Route::get('/', [JobOpeningController::class, 'index']);
        Route::get('/{jobOpening}', [JobOpeningController::class, 'show']);

        // ===== ROTAS PROTEGIDAS - ADMIN E RECRUTADOR =====
        Route::middleware(['auth:sanctum', 'role:admin,recruiter'])->group(function () {
            Route::post('/', [JobOpeningController::class, 'store']);
            Route::put('/{jobOpening}', [JobOpeningController::class, 'update']);
            Route::patch('/{jobOpening}/publish', [JobOpeningController::class, 'publish']);
            Route::patch('/{jobOpening}/close', [JobOpeningController::class, 'close']);
        });

        // ===== ROTAS PROTEGIDAS - SÓ ADMIN =====
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::delete('/{jobOpening}', [JobOpeningController::class, 'destroy']);
        });
    });
});