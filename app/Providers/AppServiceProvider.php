<?php

namespace App\Providers;

use App\Http\View\Composers\NotificationComposer;
use App\Models\LayoutJob;
use App\Models\LayoutJobPayout;
use App\Observers\LayoutJobObserver;
use App\Observers\LayoutJobPayoutObserver;
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

        // Audit trail (Andrew 2026-10-21): lahat ng galaw sa layout job at payout
        // ay awtomatikong naka-log para transparent sa board members.
        LayoutJob::observe(LayoutJobObserver::class);
        LayoutJobPayout::observe(LayoutJobPayoutObserver::class);
    }
}
