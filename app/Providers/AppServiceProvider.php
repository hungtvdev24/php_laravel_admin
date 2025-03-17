<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// Thêm use Paginator
use Illuminate\Pagination\Paginator;

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
        // Kích hoạt Bootstrap 5 cho Pagination
        Paginator::useBootstrapFive();

        // Nếu muốn dùng Bootstrap 4 thì dùng:
        // Paginator::useBootstrapFour();
    }
}
