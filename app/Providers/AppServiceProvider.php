<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;

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
        if (app()->environment('production')) {
        	URL::forceScheme('https');
        }
        
        // Use Bootstrap pagination
        \Illuminate\Pagination\Paginator::useBootstrap();

        // Register Policies
        Gate::policy(\App\Models\Property::class, \App\Policies\PropertyPolicy::class);
        Gate::policy(\App\Models\Unit::class, \App\Policies\UnitPolicy::class);
        Gate::policy(\App\Models\MaintenanceRequest::class, \App\Policies\MaintenanceRequestPolicy::class);
        Gate::policy(\App\Models\Bill::class, \App\Policies\BillPolicy::class);
        Gate::policy(\App\Models\Announcement::class, \App\Policies\AnnouncementPolicy::class);
    }
}
