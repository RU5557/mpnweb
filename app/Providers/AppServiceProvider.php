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
        // Share variabel $rollingText ke SEMUA view Blade (*)
        View::composer('*', function ($view) {
            $rollingText = RollingText::latest('tanggal')->first();
            $view->with('rollingText', $rollingText);
        });
    }
}