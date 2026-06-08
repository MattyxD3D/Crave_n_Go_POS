<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ComboController;
use App\Http\Controllers\Api\AddOnController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ReportController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}/toggle', [ProductController::class, 'toggle']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    // Combos
    Route::get('/combos', [ComboController::class, 'index']);
    Route::get('/combos/{combo}', [ComboController::class, 'show']);
    Route::post('/combos', [ComboController::class, 'store']);
    Route::put('/combos/{combo}', [ComboController::class, 'update']);
    Route::delete('/combos/{combo}', [ComboController::class, 'destroy']);

    // Add-ons
    Route::get('/add-ons', [AddOnController::class, 'index']);
    Route::post('/add-ons', [AddOnController::class, 'store']);
    Route::put('/add-ons/{addOn}', [AddOnController::class, 'update']);
    Route::delete('/add-ons/{addOn}', [AddOnController::class, 'destroy']);

    // Orders (sync must be before {order} to avoid route conflicts)
    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders/sync', [OrderController::class, 'sync']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::patch('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Inventory
    Route::get('/inventory', [InventoryController::class, 'index']);
    Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust']);
    Route::get('/inventory/{product}/logs', [InventoryController::class, 'logs']);

    // Reports
    Route::get('/reports/daily', [ReportController::class, 'daily']);
    Route::get('/reports/top-products', [ReportController::class, 'topProducts']);
    Route::get('/reports/hourly', [ReportController::class, 'hourly']);
});
