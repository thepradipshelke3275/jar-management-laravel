<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PendingItemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/* AUTH ROUTES */
Route::prefix("auth")
    ->name("auth.")
    ->controller(AuthController::class)
    ->group(function () {
        // Public routes
        Route::post('/login', 'login')->name("login");
        Route::post('/forgot-password', 'forgotPassword')->name("forgot-password");
        Route::post('/reset-password', 'resetPassword')->name("reset-password");

        // Protected routes
        Route::middleware('auth:sanctum')->group(function () {
            // Auth routes
            Route::post('/logout', 'logout')->name("logout");
            Route::get('/profile', 'profile')->name("profile");
            Route::put('/profile', 'updateProfile')->name("update-profile");
            Route::put('/change-password', 'changePassword')->name("change-password");
        });
    });

/* RESOURCE ROUTES */
Route::middleware("auth:sanctum")->group(function () {
    Route::apiResources([
        "users" => UserController::class,
        "customers" => CustomerController::class,
        "items" => ItemController::class,
        "orders" => OrderController::class,
    ]);
    Route::get("customers/{customer}/orders", [OrderController::class, "getOrdersByCustomers"]);
    Route::put("customers/{customer}/pending-items", [PendingItemController::class, "updateQuantities"]);
    Route::get("/dashboard", DashboardController::class)->name("dashboard");
});