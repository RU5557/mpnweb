<?php

use App\Http\Controllers\WpController;

Route::prefix('wp')->group(function () {
    Route::get('/masterfile/search', [WpController::class, 'searchMasterfile']);
    Route::get('/transaksi/search', [WpController::class, 'searchTransactions']);
    Route::get('/filter-options', [WpController::class, 'getFilterOptions']);
});