<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\OrganizationController;

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UserController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
    Route::get('/me', [AuthController::class, 'me']);

    // User management routes
    Route::prefix('users')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'update']);
        Route::post('/change-password', [UserController::class, 'changePassword']);
        
        // Admin and Owner routes
        Route::get('/', [UserController::class, 'index']);
        Route::delete('/{user}', [UserController::class, 'destroy']);
        
        // Owner only routes
        Route::post('/{user}/change-role', [UserController::class, 'changeRole']);
    });

    // Vendor routes
    Route::apiResource('vendors', VendorController::class);

    // Organization routes
    Route::apiResource('organizations', OrganizationController::class);
});

