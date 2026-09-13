<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WpSearchController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});
Route::get('/dashboard', function () {
    return view('dashboard.index'); // <-- Mengarah ke dashboard/index.blade.php
});


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/search-wp', [WpSearchController::class, 'search'])->name('wp.search');