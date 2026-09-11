<?php

namespace App\Providers;

use App\Http\View\Composers\NotificationComposer;
use Illuminate\Pagination\Paginator;
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
        // Share procurement notifications with the navbar layout
        View::composer('layouts.app', NotificationComposer::class);

        // Use Bootstrap 5 pagination markup app-wide. The default Tailwind view
        // renders unstyled SVGs (huge/broken arrows) because our pages load
        // Bootstrap, not Tailwind CSS.
        Paginator::useBootstrapFive();
    }
}
