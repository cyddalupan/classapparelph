<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FinanceStatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Finance API Routes (using web middleware for session-based auth)
Route::middleware(['auth', 'web'])->group(function () {
    Route::get('/expenses/statistics', [FinanceStatisticsController::class, 'statistics']);
    Route::get('/expenses/recent', [FinanceStatisticsController::class, 'recentExpenses']);
    Route::get('/expenses/chart', [FinanceStatisticsController::class, 'chartData']);
    Route::get('/expenses/{id}', [FinanceStatisticsController::class, 'getExpense']);
});