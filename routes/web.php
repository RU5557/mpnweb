<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MfwpController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::prefix('mfwp')->name('mfwp.')->group(function () {
    Route::get('/', [MfwpController::class, 'index'])->name('index');
    Route::get('/export', [MfwpController::class, 'exportCsv'])->name('export');
    Route::get('/search-klu', [MfwpController::class, 'searchKlu'])->name('search-klu');
});
