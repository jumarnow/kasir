<?php

namespace App\Providers;

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
        try {
            $settings = \App\Models\Setting::all()->pluck('value', 'key')->toArray();
            view()->share('settings', $settings);
        } catch (\Exception $e) {
            // Handle case where table doesn't exist yet (e.g. during migration)
        }
    }
}
