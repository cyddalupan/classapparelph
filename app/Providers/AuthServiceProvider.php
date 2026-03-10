<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define gates for role-based permissions
        Gate::define('access-admin', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('access-staff', function ($user) {
            return $user->isStaff() || $user->isAdmin();
        });

        Gate::define('access-customer', function ($user) {
            return $user->isCustomer() || $user->isStaff() || $user->isAdmin();
        });

        // Business-specific permissions
        Gate::define('manage-orders', function ($user) {
            return $user->isStaff() || $user->isAdmin();
        });

        Gate::define('manage-production', function ($user) {
            return $user->isStaff() || $user->isAdmin();
        });

        Gate::define('manage-inventory', function ($user) {
            return $user->isStaff() || $user->isAdmin();
        });

        Gate::define('manage-users', function ($user) {
            return $user->isAdmin();
        });

        Gate::define('manage-designs', function ($user) {
            return $user->isCustomer() || $user->isStaff() || $user->isAdmin();
        });

        Gate::define('approve-designs', function ($user) {
            return $user->isStaff() || $user->isAdmin();
        });

        Gate::define('view-reports', function ($user) {
            return $user->isStaff() || $user->isAdmin();
        });

        Gate::define('manage-finances', function ($user) {
            return $user->isAdmin();
        });

        // Sales Agent permissions
        Gate::define('access-sales-agent', function ($user) {
            return $user->isSalesAgent() || $user->isSalesRepresentative() || $user->isStaff() || $user->isAdmin();
        });

        Gate::define('input-sales', function ($user) {
            return $user->canInputSales();
        });
    }
}
