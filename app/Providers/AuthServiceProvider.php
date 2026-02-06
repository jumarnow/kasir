<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability) {
            // Admin memiliki semua akses
            if (method_exists($user, 'hasRole') && $user->hasRole('manager')) {
                return true;
            }

            // Cek apakah user punya permission
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability);
            }

            return false;
        });
    }
}
