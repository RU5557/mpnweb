<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\RollingText;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
public function boot(): void
    {
        // Bagikan data ke seluruh view TANPA me-trigger event listener berulang kali
        try {
            $rollingText = RollingText::latest('tanggal')->first();
            View::share('rollingText', $rollingText);
        } catch (\Exception $e) {
            // Menghindari error saat migrasi database belum berjalan
            View::share('rollingText', null);
        }
    }
}