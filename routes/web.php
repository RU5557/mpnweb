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
use Illuminate\Support\Facades\Storage;



// Route untuk download langsung log error dari session tanpa perlu simpan file di server
Route::get('/download-current-error-log', function () {
    $logContent = session('error_log_content', "Log error tidak ditemukan atau session telah kadaluarsa.");
    $filename   = "Error_Log_MPNWEB_" . date('Ymd_His') . ".txt";

    return response($logContent, 200, [
        'Content-Type'        => 'text/plain; charset=UTF-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Cache-Control'       => 'no-cache, must-revalidate',
    ]);
})->name('error.log.download');

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

    // Grouping Route Penjagaan
    Route::prefix('penjagaan')->name('penjagaan.')->group(function () {
        Route::get('/bulanan', [PenjagaanController::class, 'bulanan'])->name('bulanan');
        Route::get('/bulanan/export-detil', [PenjagaanController::class, 'exportBulananCsv'])->name('bulanan.export-detil');
        
        Route::get('/harian', [PenjagaanController::class, 'harian'])->name('harian');
        Route::get('/harian/export-detil', [PenjagaanController::class, 'exportHarianCsv'])->name('harian.export-detil');
        
        Route::get('/vs-bulan-lalu', [PenjagaanController::class, 'vsBulanLalu'])->name('vs-bulan-lalu');
        Route::get('/vs-bulan-lalu/export-detil', [PenjagaanController::class, 'exportVsBulanLaluCsv'])->name('vs-bulan-lalu.export-detil');
    });
});

// Route Export Data Detil Modul Lain
Route::get('/dashboard/export-detil', [DashboardController::class, 'exportDetil'])->name('dashboard.export-detil');
Route::get('/ppm/export-detil', [PpmController::class, 'exportDetil'])->name('ppm.export-detil');
Route::get('/pkm-pengawasan/export-detil', [PkmPengawasanController::class, 'exportDetil'])->name('pkm.pengawasan.export-detil');
Route::get('/pkm-pemeriksaan/export-detil', [PkmPemeriksaanController::class, 'exportDetil'])->name('pkm.pemeriksaan.export-detil');
Route::get('/pkm-penagihan/export-detil', [PkmPenagihanController::class, 'exportDetil'])->name('pkm.penagihan.export-detil');

// Fitur Search WP (URL dan Route Name diselaraskan menjadi /search-wp dan wp.search)
Route::get('/search-wp', [WpSearchController::class, 'search'])->name('wp.search');
Route::get('/search-wp/export-detil', [WpSearchController::class, 'exportCsv'])->name('wp.export-detil');

/*
|--------------------------------------------------------------------------
| Autentikasi Admin & Panel Admin
|--------------------------------------------------------------------------
*/
Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->name('admin.')->middleware(AdminAuthMiddleware::class)->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/target', [AdminController::class, 'updateTarget'])->name('target.update');
    Route::post('/rolling-text', [AdminController::class, 'updateRollingText'])->name('rolling-text.update');
});
