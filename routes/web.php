<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PpmController;
use App\Http\Controllers\WpSearchController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\PkmPengawasanController;
use App\Http\Controllers\PkmPemeriksaanController;
use App\Http\Controllers\PkmPenagihanController;

/*
|--------------------------------------------------------------------------
| Redirect Root (/)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return redirect()->route('penerimaan.dashboard');
});

/*
|--------------------------------------------------------------------------
| Modul Penerimaan Pajak (Dashboard, PPM, PKM, dll)
|--------------------------------------------------------------------------
*/
Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
    // 1. Dashboard Utama Penerimaan -> GET /penerimaan
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Modul PPM -> GET /penerimaan/ppm
    Route::get('/ppm', [PpmController::class, 'index'])->name('ppm');
    
    // 3. Modul PKM & Lainnya (Persiapan)
    // Route::get('/pkm', [PkmController::class, 'index'])->name('pkm');
});

/*
|--------------------------------------------------------------------------
| Fitur Pencarian / Search WP
|--------------------------------------------------------------------------
*/
Route::get('/search-wp', [WpSearchController::class, 'search'])->name('wp.search');

/*
|--------------------------------------------------------------------------
| Panel Admin (Target & Rolling Text)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/target', [AdminController::class, 'updateTarget'])->name('target.update');
    Route::post('/rolling-text', [AdminController::class, 'updateRollingText'])->name('rolling-text.update');
});

// Route Auth Login Admin
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Proteksi Panel Admin dengan Middleware admin.auth
Route::prefix('admin')->middleware('admin.auth')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/target', [AdminController::class, 'updateTarget'])->name('target.update');
    Route::post('/rolling-text', [AdminController::class, 'updateRollingText'])->name('rolling-text.update');
});

// Route 3: PKM Pengawasan
Route::get('/penerimaan/pkm-pengawasan', [PkmPengawasanController::class, 'index'])->name('penerimaan.pkmpengawasan');

// Route 4: PKM Pemeriksaan
Route::get('/penerimaan/pkm-pemeriksaan', [PkmPemeriksaanController::class, 'index'])->name('penerimaan.pkmpemeriksaan');

// Route 5: PKM Penagihan
Route::get('/penerimaan/pkm-penagihan', [PkmPenagihanController::class, 'index'])->name('penerimaan.pkmpenagihan');