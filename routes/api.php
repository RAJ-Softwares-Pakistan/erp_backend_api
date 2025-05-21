<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\WarehouseController;

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
    Route::prefix('vendors')->group(function () {
        Route::get('/trashed', [VendorController::class, 'trashed']);
        Route::post('/{id}/restore', [VendorController::class, 'restore']);
        Route::delete('/{id}/force', [VendorController::class, 'forceDelete']);
    });
    Route::apiResource('vendors', VendorController::class);

    // Organization routes
    Route::prefix('organizations')->group(function () {
        Route::get('/trashed', [OrganizationController::class, 'trashed']);
        Route::post('/{id}/restore', [OrganizationController::class, 'restore']);
        Route::delete('/{id}/force', [OrganizationController::class, 'forceDelete']);
    });
    Route::apiResource('organizations', OrganizationController::class);

    // Warehouse routes
    Route::prefix('warehouses')->group(function () {
        Route::get('/trashed', [WarehouseController::class, 'trashed']);
        Route::post('/{id}/restore', [WarehouseController::class, 'restore']);
        Route::delete('/{id}/force', [WarehouseController::class, 'forceDelete']);
    });
    Route::apiResource('warehouses', WarehouseController::class);
});

