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
use App\Http\Controllers\PenjagaanController;
use App\Http\Middleware\AdminAuthMiddleware;

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
| Modul Publik (Bisa diakses tanpa login)
|--------------------------------------------------------------------------
*/
Route::prefix('penerimaan')->name('penerimaan.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/ppm', [PpmController::class, 'index'])->name('ppm');
    Route::get('/pkm-pengawasan', [PkmPengawasanController::class, 'index'])->name('pkmpengawasan');
    Route::get('/pkm-pemeriksaan', [PkmPemeriksaanController::class, 'index'])->name('pkmpemeriksaan');
    Route::get('/pkm-penagihan', [PkmPenagihanController::class, 'index'])->name('pkmpenagihan');

    Route::prefix('penjagaan')->name('penjagaan.')->group(function () {
        Route::get('/bulanan', [PenjagaanController::class, 'bulanan'])->name('bulanan');
        Route::get('/bulanan/export-detil', [PenjagaanController::class, 'exportBulananCsv'])->name('bulanan.export-detil'); // Route baru
        Route::get('/harian', [PenjagaanController::class, 'harian'])->name('harian');
        Route::get('/harian/export-detil', [PenjagaanController::class, 'exportHarianCsv'])->name('harian.export');
        Route::get('/vs-bulan-lalu', [PenjagaanController::class, 'vsBulanLalu'])->name('vsbulanlalu');
    });
});
Route::get('/penerimaan/penjagaan/export-detil', [PenjagaanController::class, 'exportVsBulanLaluCsv'])->name('penerimaan.penjagaan.export-detil');


// Route Export Data Detil
Route::get('/dashboard/export-detil', [DashboardController::class, 'exportDetil'])->name('dashboard.export-detil');
Route::get('/ppm/export-detil', [PpmController::class, 'exportDetil'])->name('ppm.export-detil');
Route::get('/pkm-pengawasan/export-detil', [PkmPengawasanController::class, 'exportDetil'])->name('pkm.pengawasan.export-detil');
Route::get('/pkm-pemeriksaan/export-detil', [PkmPemeriksaanController::class, 'exportDetil'])->name('pkm.pemeriksaan.export-detil');
Route::get('/pkm-penagihan/export-detil', [PkmPenagihanController::class, 'exportDetil'])->name('pkm.penagihan.export-detil');

// Fitur Search WP
Route::get('/search-wp', [WpSearchController::class, 'search'])->name('wp.search');

/*
|--------------------------------------------------------------------------
| Autentikasi Admin
|--------------------------------------------------------------------------
*/
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Panel Admin (Dibatasi oleh AdminAuthMiddleware)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware(AdminAuthMiddleware::class)->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/target', [AdminController::class, 'updateTarget'])->name('target.update');
    Route::post('/rolling-text', [AdminController::class, 'updateRollingText'])->name('rolling-text.update');
});