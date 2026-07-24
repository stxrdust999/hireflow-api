<?php

use App\Http\Controllers\Api\Applications\ApplicationController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Comments\CommentController;
use App\Http\Controllers\Api\Companies\CompanyController;
use App\Http\Controllers\Api\Jobs\JobOpeningController;
use App\Http\Controllers\Api\Notifications\NotificationController;
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

        // ===== ROTAS PROTEGIDAS - ADMIN, RECRUTADOR E HIRING MANAGER =====
        Route::middleware(['auth:sanctum', 'role:admin,recruiter,hiring-manager'])->group(function () {
            Route::get('/{jobOpening}/applications', [ApplicationController::class, 'index']);
        });

        // ===== ROTAS PROTEGIDAS - SÓ ADMIN =====
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::delete('/{jobOpening}', [JobOpeningController::class, 'destroy']);
        });

        // ===== ROTAS PROTEGIDAS - SÓ CANDIDATO =====
        Route::middleware(['auth:sanctum', 'role:candidate'])->group(function () {
            Route::post('/{jobOpening}/applications', [ApplicationController::class, 'store']);
        });
    });

    Route::prefix('applications')->group(function () {
        // ===== ROTAS PROTEGIDAS - ADMIN, RECRUTADOR E HIRING MANAGER =====
        Route::middleware(['auth:sanctum', 'role:admin,recruiter,hiring-manager'])->group(function () {
            Route::get('/{application}', [ApplicationController::class, 'show']);

            Route::patch('/{application}/stage', [ApplicationController::class, 'move']);

            Route::get('/{application}/comments', [CommentController::class, 'index']);
            Route::post('/{application}/comments', [CommentController::class, 'store']);
        });

        // ===== ROTAS PROTEGIDAS - SÓ CANDIDATO =====
        Route::middleware(['auth:sanctum', 'role:candidate'])->group(function () {
            Route::patch('/{application}/withdraw', [ApplicationController::class, 'withdraw']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);

            Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead']);

            Route::patch('/read-all', [NotificationController::class, 'markAllAsRead']);
        });
    });

    // ===== ROTAS PROTEGIDAS - ADMIN, RECRUTADOR E HIRING MANAGER =====
    Route::middleware(['auth:sanctum', 'role:admin,recruiter,hiring-manager'])->group(function () {
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
    });

    // ===== ROTAS PROTEGIDAS - SÓ CANDIDATO =====
    Route::middleware(['auth:sanctum', 'role:candidate'])->group(function () {
        Route::get('me/applications', [ApplicationController::class, 'myApplications']);
    });

    Route::prefix('companies')->group(function () {
        // ===== ROTAS PUBLICAS =====
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/{slug}', [CompanyController::class, 'show']);

        // ===== ROTAS PROTEGIDAS - SÓ ADMIN =====
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::post('/', [CompanyController::class, 'store']);

            Route::put('/{company}', [CompanyController::class, 'update']);

            Route::delete('/{company}', [CompanyController::class, 'destroy']);
        });
    });
});