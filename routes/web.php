<?php

use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\KonfigurasiController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [KeuanganController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/recent_login', function () {
    if (auth()->user()->role !== 'admin') {
        abort(403);
    }
    $recent_login = User::orderBy('recent_login', 'desc')->get();
    return view('recent_login', compact('recent_login'));
})->middleware(['auth', 'verified'])->name('recent_login');


Route::prefix('pemasukan')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [KeuanganController::class, 'pemasukan'])->name('pemasukan');      
    Route::post('/create', [KeuanganController::class, 'pemasukanCreate'])->name('create');
    Route::put('/{id}', [KeuanganController::class, 'pemasukanUpdate'])->name('update');
    Route::delete('/{id}', [KeuanganController::class, 'pemasukanDestroy'])->name('destroy');
})->middleware(['auth', 'verified']);

Route::prefix('pengeluaran')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [KeuanganController::class, 'pengeluaran'])->name('pengeluaran');      
    Route::post('/create', [KeuanganController::class, 'pengeluaranCreate'])->name('create');
    Route::put('/{id}', [KeuanganController::class, 'pengeluaranUpdate'])->name('update');
    Route::delete('/{id}', [KeuanganController::class, 'pengeluaranDestroy'])->name('destroy');
})->middleware(['auth', 'verified']);

Route::prefix('user')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('user');      
    Route::post('/create', [UserController::class, 'create'])->name('create');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
})->middleware(['auth', 'verified']);

Route::prefix('pengumuman')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PengumumanController::class, 'index'])->name('pengumuman');      
    Route::post('/pengumuman', [PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::delete('/destroyAll', [PengumumanController::class, 'destroyAll'])->name('pengumuman.destroyAll');
    Route::put('/{id}', [PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/{id}', [PengumumanController::class, 'destroy'])->name('pengumuman.destroy');
})->middleware(['auth', 'verified']);

Route::prefix('konfigurasi')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [KonfigurasiController::class, 'index'])->name('konfigurasi');
    Route::put('/update', [KonfigurasiController::class, 'update'])->name('konfigurasi.update');
})->middleware(['auth', 'verified']);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



require __DIR__.'/auth.php';
