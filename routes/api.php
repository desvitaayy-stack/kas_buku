<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KeuanganController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum')->get('/me', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/{id?}', [UserController::class, 'index']);
    Route::get('/keuangan', [KeuanganController::class, 'index']);
    Route::get('/pemasukan', [KeuanganController::class, 'pemasukan']);
    Route::get('/pengeluaran', [KeuanganController::class, 'pengeluaran']);
    Route::post('/keuangan', [KeuanganController::class, 'store']);
    Route::put('/keuangan/{id}', [KeuanganController::class, 'update']);
    Route::delete('/keuangan/{id}', [KeuanganController::class, 'destroy']);
    Route::get('/saldo', [KeuanganController::class, 'cekSaldo']);
    Route::get('/laporan', [KeuanganController::class, 'laporan']);
});

Route::get('/dashboard', [DashboardController::class, 'index']);
Route::get('/dashboard/saldo', [DashboardController::class, 'cekSaldo']);
Route::get('/dashboard/laporan', [DashboardController::class, 'laporan']);
Route::get('/dashboard/chart', [DashboardController::class, 'chart']);
Route::middleware('auth:sanctum')->post('/fcm-token', function (Request $request) {
    $request->validate(['fcm_token' => 'required|string']);
    $request->user()->update(['fcm_token' => $request->fcm_token]);
    return response()->json(['success' => true]);
});

// Route::prefix('v1')->group(function () {
//     Route::get('/pemasukan', [KeuanganController::class, 'pemasukan']);
//     Route::get('/pengeluaran', [KeuanganController::class, 'pengeluaran']);
//     Route::post('/keuangan', [KeuanganController::class, 'store']);
//     Route::put('/keuangan/{id}', [KeuanganController::class, 'update']);
//     Route::delete('/keuangan/{id}', [KeuanganController::class, 'destroy']);
// });
