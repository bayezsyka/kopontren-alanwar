<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\POSController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ReportController;

Route::get('/ping', fn() => response()->json(['ok' => true]));

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'kasir.autologout'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/me/mode', [AuthController::class, 'setMode']); // owner switch mode
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Kasir core
    Route::get('/items', [ItemController::class, 'index']);
    Route::post('/items', [ItemController::class, 'store']);
    Route::put('/items/{item}', [ItemController::class, 'update']);
    Route::post('/items/{item}/bundle-components', [ItemController::class, 'setBundleComponents']);

    Route::post('/sales', [POSController::class, 'storeSale']);
    Route::post('/purchases', [PurchaseController::class, 'store']);

    Route::get('/stock/low', [StockController::class, 'low']);

    // Owner only (dashboard + reports + adjust stock)
    Route::middleware(['owner.only'])->group(function () {
        Route::post('/stock/adjust', [StockController::class, 'adjust']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/dashboard/series', [DashboardController::class, 'series']);
        Route::get('/dashboard/top-items', [DashboardController::class, 'topItems']);

        Route::get('/reports/weekly', [ReportController::class, 'weeklyList']);
        Route::get('/reports/weekly/{report}', [ReportController::class, 'weeklyDetail']);
        Route::post('/reports/weekly/{report}/generate', [ReportController::class, 'generateWeeklyPdf']);
        Route::get('/reports/weekly/{report}/download', [ReportController::class, 'downloadWeeklyPdf']);
    });
});
