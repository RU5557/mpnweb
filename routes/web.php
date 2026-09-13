<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WpSearchController;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});
Route::get('/dashboard', function () {
    return view('dashboard.index'); // <-- Mengarah ke dashboard/index.blade.php
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/search-wp', [WpSearchController::class, 'search'])->name('wp.search');

// ROUTE PANEL ADMIN (INPUT TARGET & ROLLING TEXT)
Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::post('/admin/target', [AdminController::class, 'updateTarget'])->name('admin.target.update');
Route::post('/admin/rolling-text', [AdminController::class, 'updateRollingText'])->name('admin.rolling-text.update');