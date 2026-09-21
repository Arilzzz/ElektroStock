<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);


    Route::apiResource('/categories', CategoryController::class);
    Route::apiResource('brands', BrandController::class);
    Route::apiResource('products', ProductController::class);

    // Stock
    Route::post('/stock/in', [StockController::class, 'stockIn']);
    Route::post('/stock/out', [StockController::class, 'stockOut']);

    Route::get('/stock/history', [
        StockController::class,
        'history'
    ]);

    Route::get('/stock/history/{stockTransaction}', [
        StockController::class,
        'show'
    ]);

    Route::prefix('reports')->group(function () {
        Route::get('/stock', [
            ReportController::class,
            'stock'
        ]);

        Route::get('/transactions', [
            ReportController::class,
            'transactions'
        ]);

        Route::get('/profit', [
            ReportController::class,
            'profit'
        ]);
    });
});
