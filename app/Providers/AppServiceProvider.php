<?php

namespace App\Providers;

use App\Models\RollingText;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        // Bagikan variabel $rollingText ke SELURUH view di aplikasi
        View::composer('*', function ($view) {
            $rollingText = RollingText::latest('id')->first();
            $view->with('rollingText', $rollingText);
        });
    }
}
